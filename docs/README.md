# 🏖️ Nattier Store - Moda de Playa

**E-commerce de bikinis y moda de playa con sistema de pago simulado tipo MercadoPago**

## ✨ Características Principales

- 💳 **Sistema de pago simulado** con validación completa de tarjetas
- 🛒 **Carrito de compras** persistente  
- 👤 **Sistema de usuarios** (clientes y administradores)
- 📱 **Diseño responsive** para móviles y desktop
- � **Validaciones de seguridad** y autenticación

## � Instalación Rápida

### 1. Base de Datos
```sql
-- En phpMyAdmin o MySQL, ejecuta:
SOURCE database_fresh_install.sql;
```

### 2. Servidor Web
- Asegurar que Laragon/XAMPP esté ejecutándose
- Abrir: `http://localhost/Nattier_Store/ecommerce_artesanias.html`

## 🔑 Usuarios de Prueba

**Administrador:**
- Email: `admin@nattierstore.com`
- Contraseña: `admin123`

**Cliente:**
- Email: `cliente@test.com`  
- Contraseña: `cliente123`

## 💳 Tarjetas de Prueba

```
Visa: 4532 1234 5678 9010
MasterCard: 5555 5555 5555 4444
CVV: 123 | Fecha: 12/26 | Titular: TU NOMBRE
```

## 📁 Estructura del Proyecto

```
Nattier_Store/
├── ecommerce_artesanias.html    # Aplicación principal
├── api/                         # Backend PHP
│   ├── login.php               # Autenticación
│   ├── registro.php            # Registro de usuarios
│   ├── productos.php           # CRUD productos  
│   ├── carrito.php             # Gestión carrito
│   ├── pedidos.php             # Procesamiento pedidos
│   ├── forgot_password.php     # Recuperar contraseña
│   └── reset_password.php      # Resetear contraseña
├── config/
│   └── database.php            # Conexión BD
├── fotos/                      # Imágenes productos
├── database_fresh_install.sql  # Script BD completo
└── README.md                   # Este archivo
```

## �️ Productos Disponibles

- **17 productos** de moda de playa
- **Rangos de precio:** $25.000 - $190.000
- **Categorías:** Ropa, Accesorios, Decoración
- **Stock controlado** automáticamente

## 🎯 Funcionalidades

✅ Catálogo de productos con filtros
✅ Carrito de compras persistente
✅ Sistema de pago con validación de tarjetas  
✅ Panel de administración completo
✅ Gestión de pedidos y usuarios
✅ Recuperación de contraseña
✅ Control de inventario automático

---

**¡Tu tienda de moda de playa está lista para usar!** �️
