# --- STAGE 1: BUILDER ---
# Di stage ini, kita instal alat 'build' dan meng-compile paket python
FROM python:3.10-slim AS builder

# Set working directory
WORKDIR /app

# Instal alat build (HANYA di stage ini)
# Kita tidak perlu libpq-dev
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    && rm -rf /var/lib/apt/lists/*

# Buat virtual environment
RUN python -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"

# Instal dependensi Python (aioredis akan diinstal di sini)
# Ini akan meng-compile uvloop menggunakan build-essential
RUN pip install --no-cache-dir \
    websockets \
    python-dotenv \
    httpx \
    uvloop \
    ocpp==0.8.1 \
    aioredis

# --- STAGE 2: FINAL IMAGE ---
# Ini adalah image akhir yang bersih dan kecil, tanpa alat 'build'
FROM python:3.10-slim

# Environment settings
ENV PYTHONUNBUFFERED=1 \
    PYTHONDONTWRITEBYTECODE=1

WORKDIR /app

# Salin HANYA paket python yang sudah terinstal dari stage 'builder'
COPY --from=builder /opt/venv /opt/venv

# Salin kode aplikasi Anda
COPY . /app

# Atur PATH untuk menggunakan virtual environment
ENV PATH="/opt/venv/bin:$PATH"

# Expose port
EXPOSE 9001

# Run the OCPP server
CMD ["python", "server.py"]