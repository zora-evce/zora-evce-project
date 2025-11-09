# Use a lightweight Python base image
FROM python:3.12-slim

# Prevent Python from buffering stdout and writing pyc files
ENV PYTHONUNBUFFERED=1 \
    PYTHONDONTWRITEBYTECODE=1

# Set working directory inside container
WORKDIR /app

# Install required system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Install Python dependencies
# If you have a requirements.txt file, uncomment these two lines:
# COPY requirements.txt /app/
# RUN pip install --no-cache-dir -r requirements.txt

# Otherwise, install common libraries manually
RUN pip install --no-cache-dir \
    websockets \
    python-dotenv \
    psycopg2-binary \
    uvloop

# Copy all project files into container
COPY . /app

# Expose OCPP WebSocket port
EXPOSE 9000

# Command to start the OCPP server
CMD ["python", "server.py"]
