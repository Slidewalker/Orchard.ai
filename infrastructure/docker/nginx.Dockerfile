FROM nginx:alpine
COPY orchard-dashboard.html /usr/share/nginx/html/index.html
COPY assets/ /usr/share/nginx/html/assets/
COPY infrastructure/docker/nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
