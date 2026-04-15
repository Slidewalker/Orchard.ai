@echo off
title Orchard.ai Launcher
cd /d "%~dp0"

echo ============================================
echo   Orchard.ai - Sovereign Local AI Startup
echo ============================================
echo.

REM Check Docker is running
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker is not running. Please start Docker Desktop first.
    pause
    exit /b 1
)
echo [OK] Docker is running.

REM Build and start all services
echo.
echo Starting all services (this may take a few minutes on first run)...
docker compose up -d --build

if %errorlevel% neq 0 (
    echo [ERROR] docker compose failed. Check the output above.
    pause
    exit /b 1
)

echo.
echo [OK] Services started.
echo.

REM Wait for Ollama to be ready
echo Waiting for Ollama to be ready...
timeout /t 5 /nobreak >nul

REM Pull the sovereign local AI model
echo Pulling sovereign AI model (llama3.2:1b) into Ollama...
docker compose exec ollama ollama pull llama3.2:1b

echo.
echo ============================================
echo   Orchard.ai is running!
echo ============================================
echo   Dashboard : http://localhost:8080
echo   AI Status : http://localhost:8080/api/ai/status
echo   RabbitMQ  : http://localhost:15672
echo ============================================
echo.

REM Open dashboard in browser
start "" "http://localhost:8080"

pause
