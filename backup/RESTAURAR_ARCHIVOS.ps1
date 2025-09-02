# 🔄 SCRIPT DE RESTAURACIÓN - Nattier Store
# Ejecutar este script si necesitas deshacer la limpieza

Write-Host "🔄 RESTAURANDO ARCHIVOS DESDE RESPALDO..." -ForegroundColor Yellow

$carpetaRespaldo = "RESPALDO_20250901_223749"

if (Test-Path $carpetaRespaldo) {
    # Restaurar todos los archivos del respaldo
    Copy-Item "$carpetaRespaldo\*" -Destination "." -Force
    
    Write-Host "✅ ARCHIVOS RESTAURADOS EXITOSAMENTE" -ForegroundColor Green
    Write-Host "📂 Archivos restaurados desde: $carpetaRespaldo" -ForegroundColor Cyan
    
    # Mostrar archivos restaurados
    Write-Host "`n📋 ARCHIVOS RESTAURADOS:" -ForegroundColor White
    Get-ChildItem $carpetaRespaldo | ForEach-Object { Write-Host "   ✓ $($_.Name)" -ForegroundColor Green }
    
    Write-Host "`n🎯 ESTADO: Sistema restaurado al estado anterior a la limpieza" -ForegroundColor Green
} else {
    Write-Host "❌ ERROR: No se encontró la carpeta de respaldo: $carpetaRespaldo" -ForegroundColor Red
}

Write-Host "`n🧪 RECOMENDACIÓN: Prueba el sistema para verificar que todo funcione correctamente" -ForegroundColor Yellow
