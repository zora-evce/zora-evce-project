# ocpp-server/Dockerfile
FROM python:3.10-slim

# Set working directory
WORKDIR /app

# Install dependencies
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy source code
COPY . .

# Expose port (change 9000 to the actual OCPP server port)
EXPOSE 9000

# Run the server
CMD ["python", "main.py"]