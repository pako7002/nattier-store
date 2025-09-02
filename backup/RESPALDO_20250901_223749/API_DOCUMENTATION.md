# 📚 Documentación de API - Nattier Store

## 🔗 Endpoints Disponibles

### 🔐 **Autenticación**

#### **POST** `/api/login.php`
Iniciar sesión de usuario.

**Request:**
```json
{
    "email": "admin@nattierstore.com",
    "password": "admin123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Inicio de sesión exitoso",
    "user": {
        "id": 1,
        "nombre": "Administrador",
        "email": "admin@nattierstore.com",
        "rol": "admin"
    }
}
```

#### **POST** `/api/registro.php`
Registrar nuevo usuario.

**Request:**
```json
{
    "nombre": "Juan Pérez",
    "email": "juan@example.com",
    "password": "mipassword123",
    "telefono": "3001234567",
    "direccion": "Calle 123 #45-67"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Usuario registrado exitosamente",
    "user": {
        "id": 3,
        "nombre": "Juan Pérez",
        "email": "juan@example.com",
        "rol": "cliente"
    }
}
```

### 🔄 **Recuperación de Contraseña**

#### **POST** `/api/forgot_password.php`
Solicitar reseteo de contraseña.

**Request:**
```json
{
    "email": "cliente@test.com"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Se han enviado las instrucciones a tu email",
    "token_for_testing": "abc123..." // Solo en desarrollo
}
```

#### **POST** `/api/reset_password.php`
Restablecer contraseña con token.

**Request:**
```json
{
    "token": "token_de_64_caracteres",
    "password": "nueva_contraseña_123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Contraseña actualizada exitosamente"
}
```

### 🛍️ **Productos**

#### **GET** `/api/productos.php`
Obtener lista de productos.

**Query Parameters:**
- `sort` (opcional): `price_asc`, `price_desc`, `default`

**Response:**
```json
[
    {
        "id": 1,
        "nombre": "Suéter Artesanal",
        "descripcion": "Hermoso suéter tejido a mano",
        "precio": "85000.00",
        "stock": 10,
        "imagen": "fotos/FOTO1.jpeg",
        "categoria_id": 1,
        "categoria_nombre": "Ropa"
    }
]
```

#### **POST** `/api/productos.php`
Crear o actualizar producto (solo admin).

**Headers:**
- `Authorization: Bearer {user_id}`

**Form Data:**
```
action: "create" | "update"
nombre: "Nombre del producto"
descripcion: "Descripción"
precio: 50000
stock: 15
categoria_id: 1
imagen: [archivo de imagen]
```

#### **DELETE** `/api/productos.php?id={product_id}`
Eliminar producto (solo admin).

### 🛒 **Carrito**

#### **GET** `/api/carrito.php`
Obtener carrito del usuario.

**Headers:**
- `Authorization: Bearer {user_id}`

**Response:**
```json
{
    "success": true,
    "cart": [
        {
            "id": 1,
            "usuario_id": 2,
            "producto_id": 1,
            "cantidad": 2,
            "nombre": "Suéter Artesanal",
            "precio": "85000.00",
            "imagen": "fotos/FOTO1.jpeg"
        }
    ]
}
```

#### **POST** `/api/carrito.php`
Agregar producto al carrito.

**Request:**
```json
{
    "producto_id": 1,
    "cantidad": 2
}
```

#### **PUT** `/api/carrito.php`
Actualizar cantidad en carrito.

**Request:**
```json
{
    "producto_id": 1,
    "cantidad": 3
}
```

#### **DELETE** `/api/carrito.php?producto_id={id}`
Eliminar producto del carrito.

### 📦 **Pedidos**

#### **GET** `/api/pedidos.php`
Obtener pedidos del usuario.

**Response:**
```json
{
    "success": true,
    "orders": [
        {
            "id": 1,
            "total": "170000.00",
            "estado": "pendiente",
            "estado_pago": "pagado",
            "metodo_pago": "mercadopago",
            "fecha": "2025-09-01 21:30:00"
        }
    ]
}
```

#### **GET** `/api/pedidos.php?id={order_id}`
Obtener detalles de un pedido específico.

#### **POST** `/api/pedidos.php`
Crear nuevo pedido.

**Request:**
```json
{
    "cart": [
        {
            "id": 1,
            "quantity": 2,
            "precio": "85000.00",
            "nombre": "Suéter Artesanal"
        }
    ],
    "total": 170000,
    "datos_entrega": {
        "nombre": "Juan Pérez",
        "email": "juan@example.com",
        "telefono": "3001234567",
        "direccion": "Calle 123",
        "metodo_pago": "mercadopago",
        "tarjeta": {
            "numero": "4532123456789010",
            "fecha_exp": "12/26",
            "cvv": "123",
            "titular": "JUAN PEREZ",
            "cuotas": "1"
        }
    },
    "estado_pago": "pagado"
}
```

## 🔑 **Autenticación**

Todos los endpoints protegidos requieren el header:
```
Authorization: Bearer {user_id}
```

## 📊 **Códigos de Estado HTTP**

- `200` - Éxito
- `400` - Datos inválidos
- `401` - No autorizado
- `403` - Acceso denegado
- `404` - No encontrado
- `405` - Método no permitido
- `500` - Error interno del servidor

## 🧪 **Datos de Prueba**

### **Usuarios:**
- **Admin:** `admin@nattierstore.com` / `admin123`
- **Cliente:** `cliente@test.com` / `cliente123`

### **Tarjetas de Prueba:**
```
Visa: 4532 1234 5678 9010
MasterCard: 5555 5555 5555 4444
American Express: 3782 822463 10005
```

### **Datos de Tarjeta:**
- **CVV:** `123`
- **Fecha:** `12/26` (cualquier fecha futura)
- **Titular:** Cualquier nombre en mayúsculas
