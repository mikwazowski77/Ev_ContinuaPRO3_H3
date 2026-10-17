<?php
$apiUrl = "http://localhost:4500/api/productos";

// Procesar alta o edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        "nombre" => trim($_POST['nombre']),
        "proveedor" => trim($_POST['proveedor']),
        "fecha_vencimiento" => $_POST['fecha_vencimiento'],
        "precio" => (float)$_POST['precio'],
        "stock" => (int)$_POST['stock']
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if (!empty($_POST['id'])) {
        curl_setopt($ch, CURLOPT_URL, $apiUrl . "/" . $_POST['id']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    }

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
    header("Location: index.php");
    exit;
}

// Procesar baja lógica
if (isset($_GET['baja'])) {
    $ch = curl_init($apiUrl . "/" . $_GET['baja']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_exec($ch);
    curl_close($ch);
    header("Location: index.php");
    exit;
}

// Obtener listado de la API
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
curl_close($ch);

// Decodificar asegurando que $lista sea siempre un array
$lista = [];
if ($res) {
    $decoded = json_decode($res, true);
    if (is_array($decoded)) {
        $lista = $decoded;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minimarket - Gestión de Inventario</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #fcf8f5;
            color: #2d2b2a;
            padding: 40px 20px;
        }

        .main-wrapper {
            max-width: 900px;
            margin: 0 auto;
        }

        .app-header {
            background: #c2410c;
            color: #ffffff;
            padding: 22px 28px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 4px 10px rgba(194, 65, 12, 0.15);
        }

        .app-header h1 {
            font-size: 21px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .app-header p {
            font-size: 13px;
            color: #ffedd5;
            margin-top: 4px;
        }

        .panel-card {
            background: #ffffff;
            border: 1px solid #fed7aa;
            border-radius: 10px;
            padding: 22px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        }

        .panel-title {
            font-size: 14px;
            font-weight: 700;
            color: #9a3412;
            margin-bottom: 18px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 16px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
        }

        .input-group label {
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 6px;
            color: #7c2d12;
        }

        .input-field {
            padding: 9px 12px;
            border: 1px solid #fdba74;
            border-radius: 6px;
            font-size: 13px;
            background-color: #fffdfb;
            transition: all 0.2s ease;
        }

        .input-field:focus {
            border-color: #ea580c;
            background-color: #ffffff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.15);
        }

        .btn-main {
            background-color: #ea580c;
            color: #ffffff;
            border: none;
            padding: 11px 22px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 18px;
            transition: background-color 0.2s ease;
        }

        .btn-main:hover {
            background-color: #c2410c;
        }

        .table-container {
            background: #ffffff;
            border: 1px solid #fed7aa;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13px;
        }

        th {
            background-color: #fff7ed;
            color: #9a3412;
            font-weight: 700;
            padding: 14px 16px;
            border-bottom: 2px solid #ffedd5;
        }

        td {
            padding: 13px 16px;
            border-bottom: 1px solid #fff7ed;
            color: #44403c;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: #fffaf5;
        }

        .btn-badge {
            padding: 6px 14px;
            font-size: 11px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            border: none;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        /* Botón Editar: Azul Cobalto */
        .btn-badge-edit {
            background-color: #2563eb;
            color: #ffffff;
        }

        .btn-badge-edit:hover {
            background-color: #1d4ed8;
        }

        /* Botón Eliminar: Pizarra Oscuro */
        .btn-badge-delete {
            background-color: #475569;
            color: #ffffff;
        }

        .btn-badge-delete:hover {
            background-color: #334155;
        }

        .empty-state {
            text-align: center;
            color: #a8a29e;
            padding: 25px;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="app-header">
        <h1>Sistema de Control de Inventario</h1>
        <p>Panel de Administración - Miguel Calle (Hito 3)</p>
    </div>

    <!-- Formulario -->
    <div class="panel-card">
        <div class="panel-title" id="formTitle">Registro de Producto</div>
        <form method="POST">
            <input type="hidden" name="id" id="prodId">
            
            <div class="form-grid">
                <div class="input-group">
                    <label>Nombre:</label>
                    <input type="text" name="nombre" id="prodNombre" class="input-field" placeholder="Ej. Fideos 500g" required>
                </div>
                <div class="input-group">
                    <label>Proveedor:</label>
                    <input type="text" name="proveedor" id="prodProveedor" class="input-field" placeholder="Ej. Distribuidora Central" required>
                </div>
                <div class="input-group">
                    <label>Vencimiento:</label>
                    <input type="date" name="fecha_vencimiento" id="prodFecha" class="input-field" required>
                </div>
                <div class="input-group">
                    <label>Precio (Bs):</label>
                    <input type="number" step="0.01" name="precio" id="prodPrecio" class="input-field" placeholder="0.00" required>
                </div>
                <div class="input-group">
                    <label>Stock:</label>
                    <input type="number" name="stock" id="prodStock" class="input-field" placeholder="0" required>
                </div>
            </div>

            <button type="submit" class="btn-main">Guardar</button>
        </form>
    </div>

    <!-- Tabla -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Proveedor</th>
                    <th>Vencimiento</th>
                    <th>Precio</th>
                    <th>Stock</th>
        
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lista) || !is_array($lista)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">No existen productos activos registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($lista as $item): ?>
                    <tr>
                        <td><strong>#<?= $item['id'] ?? '' ?></strong></td>
                        <td><?= htmlspecialchars($item['nombre'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['proveedor'] ?? '') ?></td>
                        <td><?= $item['fecha_vencimiento'] ?? '' ?></td>
                        <td>Bs. <?= number_format($item['precio'] ?? 0, 2) ?></td>
                        <td><?= $item['stock'] ?? 0 ?> unids.</td>
                        <td style="text-align: center;">
                            <button type="button" class="btn-badge btn-badge-edit" onclick='cargarRegistro(<?= json_encode($item) ?>)'>Editar</button>
                            <a href="?baja=<?= $item['id'] ?? '' ?>" class="btn-badge btn-badge-delete" onclick="return confirm('¿Dar de baja este registro?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function cargarRegistro(item) {
    document.getElementById('formTitle').innerText = 'Actualizar Producto #' + item.id;
    document.getElementById('prodId').value = item.id;
    document.getElementById('prodNombre').value = item.nombre;
    document.getElementById('prodProveedor').value = item.proveedor;
    document.getElementById('prodFecha').value = item.fecha_vencimiento;
    document.getElementById('prodPrecio').value = item.precio;
    document.getElementById('prodStock').value = item.stock;
}
</script>

</body>
</html>