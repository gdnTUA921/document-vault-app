# Use Node.js image
FROM node:20

# Create app directory
WORKDIR /app

# Install dependencies
COPY package*.json ./
RUN npm install

# Copy source
COPY . .

# Expose port for Vite dev server
EXPOSE 5173

# Run in dev mode
CMD ["npm", "run", "dev", "--", "--host"]
