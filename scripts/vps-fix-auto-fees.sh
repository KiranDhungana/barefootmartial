#!/usr/bin/env bash
# Fix bad auto monthly fees + deploy fee settings on VPS
# Usage (from project root on VPS):
#   bash scripts/vps-fix-auto-fees.sh
#   bash scripts/vps-fix-auto-fees.sh --purge   # also delete unpaid auto invoices

set -euo pipefail

PURGE=0
if [[ "${1:-}" == "--purge" ]]; then
  PURGE=1
fi

cd "$(dirname "$0")/.."

echo "==> Project: $(pwd)"

# 1) Ensure auto-fee flags are off in .env
touch .env
grep -q '^ACADEMY_MONTHLY_FEE_AUTO_GENERATE=' .env \
  && sed -i 's/^ACADEMY_MONTHLY_FEE_AUTO_GENERATE=.*/ACADEMY_MONTHLY_FEE_AUTO_GENERATE=false/' .env \
  || echo 'ACADEMY_MONTHLY_FEE_AUTO_GENERATE=false' >> .env

grep -q '^ACADEMY_MONTHLY_FEE_BACKFILL=' .env \
  && sed -i 's/^ACADEMY_MONTHLY_FEE_BACKFILL=.*/ACADEMY_MONTHLY_FEE_BACKFILL=false/' .env \
  || echo 'ACADEMY_MONTHLY_FEE_BACKFILL=false' >> .env

echo "==> .env fee flags set to false"

# 2) Pull latest code if this is a git deploy (optional — comment out if you upload manually)
# git pull origin main

# 3) Install / migrate / clear caches
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache

# 4) Show what the broken daily generator would still try to create (should be ignored while auto=false)
php artisan academy:generate-monthly-fees --dry-run || true

# 5) Optional: delete unpaid auto-generated monthly invoices
if [[ "$PURGE" -eq 1 ]]; then
  echo "==> Dry-run purge first"
  php artisan academy:purge-auto-monthly-invoices --dry-run
  echo "==> Purging unpaid auto monthly invoices"
  php artisan academy:purge-auto-monthly-invoices --force
fi

# 6) Confirm cron still runs the scheduler (reminders/backup only; fees stay off)
echo ""
echo "==> Cron must include (once):"
echo '* * * * * cd '"$(pwd)"' && php artisan schedule:run >> /dev/null 2>&1'
echo ""
echo "==> Done. Auto monthly fees are OFF."
echo "    Create invoices manually in ERP → Invoices."
echo "    To re-enable later: ACADEMY_MONTHLY_FEE_AUTO_GENERATE=true (keep BACKFILL=false)"
