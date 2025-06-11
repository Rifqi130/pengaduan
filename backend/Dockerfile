# Gunakan image Node.js LTS
FROM node:18-alpine

# Set working directory
WORKDIR /app

# Salin package.json dan package-lock.json
COPY package*.json ./

# Install dependencies
RUN npm install

# Salin seluruh source code ke dalam container
COPY . .

# Expose port (default: 3000)
EXPOSE 3000

# Jalankan aplikasi
CMD ["npm", "start"]
