# Orchard.ai Kubernetes Setup Script
# This script prepares the local environment and deploys the Orchard.ai stack.

$namespace = "orchard"

Write-Host "--- Checking Prerequisites ---" -ForegroundColor Cyan

# Check for Minikube
if (!(Get-Command minikube -ErrorAction SilentlyContinue)) {
    Write-Host "Minikube not found. Installing via winget..." -ForegroundColor Yellow
    winget install -e --id Kubernetes.minikube
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Failed to install Minikube. Please install it manually."
        exit
    }
}

# Start Minikube
Write-Host "--- Starting Minikube ---" -ForegroundColor Cyan
minikube start --driver=docker

# Point shell to minikube's docker-env
Write-Host "--- Configuring Docker Environment ---" -ForegroundColor Cyan
& minikube -p minikube docker-env --shell powershell | Out-String | Invoke-Expression

# Build the Backend Image
Write-Host "--- Building Laravel Backend Image ---" -ForegroundColor Cyan
docker build -t orchard/backend:latest ./backend/laravel

# Create Namespace
Write-Host "--- Creating Namespace: $namespace ---" -ForegroundColor Cyan
kubectl create namespace $namespace --dry-run=client -o yaml | kubectl apply -f -

# Apply Manifests
Write-Host "--- Applying Kubernetes Manifests ---" -ForegroundColor Cyan
kubectl apply -f ./infrastructure/kubernetes/configmap.yaml
kubectl apply -f ./infrastructure/kubernetes/secrets.yaml
kubectl apply -f ./infrastructure/kubernetes/mysql.yaml
kubectl apply -f ./infrastructure/kubernetes/rabbitmq.yaml
kubectl apply -f ./infrastructure/kubernetes/service.yaml
kubectl apply -f ./infrastructure/kubernetes/deployment.stateless.yaml

Write-Host "`n--- Deployment Complete ---" -ForegroundColor Green
Write-Host "To access the backend, run: kubectl port-forward -n $namespace service/backend 8080:80"
Write-Host "Then visit: http://localhost:8080"
