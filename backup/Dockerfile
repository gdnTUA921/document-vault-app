# Use Debian as base for apt-get availability
FROM debian:bullseye-slim

# Install required tools
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    cron \
    bash \
    coreutils \
    tzdata \
    && rm -rf /var/lib/apt/lists/*

# Copy backup script
COPY backup.sh /backup.sh
RUN chmod +x /backup.sh

# Add cron job: run every day at 2AM
RUN echo "0 2 * * * /backup.sh >> /var/log/cron.log 2>&1" > /etc/cron.d/backup
RUN chmod 0644 /etc/cron.d/backup

# Apply cron job
RUN crontab /etc/cron.d/backup

# Run cron in foreground
CMD ["cron", "-f"]
