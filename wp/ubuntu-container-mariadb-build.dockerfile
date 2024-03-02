# mariadb image which supports both amd64 & arm64 architecture
FROM mariadb:10.6.4-focal

# Example: Copy a custom MySQL configuration file into the container
# COPY my-custom.cnf /etc/mysql/conf.d/

# You can add additional commands here to customize the image