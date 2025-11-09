# Use a lightweight Python base image
FROM python:3.12-slim

# Environment settings for cleaner logs and faster startup
ENV PYTHONUNBUFFERED=1 \
    PYTHONDONTWRITEBYTECODE=1

# Set working directory inside container
WORKDIR /app

# Install required system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Install Python dependencies (including httpx)
RUN pip install --no-cache-dir \
    websockets \
    python-dotenv \
    psycopg2-binary \
    httpx \
    uvloop

# Copy all source code from your project folder into the container
COPY . /app

# Expose the WebSocket port used by your OCPP server
EXPOSE 9001

# Run the OCPP server
CMD ["python", "server.py"]
