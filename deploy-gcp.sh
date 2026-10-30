#!/usr/bin/env bash
# ==============================================================================
# PADAIDI RENT CAR - GOOGLE CLOUD RUN DEPLOYMENT (BASH / CLOUD SHELL)
# ==============================================================================
set -e

echo "=========================================================="
echo "  PADAIDI RENT CAR - GOOGLE CLOUD RUN DEPLOYMENT WIZARD"
echo "=========================================================="

REGION="asia-southeast2"
PROJECT_ID=$(gcloud config get-value project 2>/dev/null)

if [ -z "$PROJECT_ID" ] || [ "$PROJECT_ID" = "(unset)" ]; then
    echo "Pilih project Google Cloud Anda:"
    gcloud projects list
    read -p "Masukkan Project ID: " PROJECT_ID
    gcloud config set project "$PROJECT_ID"
fi

echo "[✓] Using GCP Project: $PROJECT_ID"
echo "[✓] Region: $REGION (Jakarta, Indonesia)"

echo "[*] Enabling required GCP APIs..."
gcloud services enable run.googleapis.com cloudbuild.googleapis.com artifactregistry.googleapis.com --project="$PROJECT_ID"

echo "[*] Deploying to Google Cloud Run via Cloud Build..."
gcloud run deploy padaidi-web \
    --source . \
    --region "$REGION" \
    --project "$PROJECT_ID" \
    --platform managed \
    --allow-unauthenticated \
    --port 8080 \
    --memory 512Mi \
    --cpu 1 \
    --min-instances 0 \
    --max-instances 10 \
    --set-env-vars "APP_NAME=Padaidi Rent Car,APP_ENV=production,APP_DEBUG=false,APP_TIMEZONE=Asia/Makassar,APP_LOCALE=id,AUTO_MIGRATE=true,AUTO_SEED=true,SESSION_DRIVER=database,CACHE_STORE=database,FILESYSTEM_DISK=public"

SERVICE_URL=$(gcloud run services describe padaidi-web --platform managed --region "$REGION" --format "value(status.url)")

echo ""
echo "=========================================================="
echo "  DEPLOYMENT BERHASIL!"
echo "  URL Website: $SERVICE_URL"
echo "=========================================================="

