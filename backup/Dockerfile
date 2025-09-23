FROM alpine:3.18

RUN apk add --no-cache mysql-client bash coreutils tzdata

# Copy backup script
COPY backup.sh /backup.sh
RUN chmod +x /backup.sh

# Cronjob at 2 AM Manila time
ENV TZ=Asia/Manila
RUN echo "0 2 * * * /backup.sh >> /var/log/cron.log 2>&1" > /etc/crontabs/root

CMD ["crond", "-f", "-d", "8"]
