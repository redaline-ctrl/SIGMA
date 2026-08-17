<?php
$usuarios = $usuarios ?? [];
$roles = $roles ?? [];
?>
<div class="dashboard-page">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div><h1 class="h4 mb-1">Usuarios y permisos</h1><p class="text-muted mb-0">Administra cuentas sin borrar su historial operativo.</p></div>
    </div>
    <div class="card shadow-sm border-0 mb-4"><div class="card-body">
        <h2 class="h6">Crear usuario</h2>
        <form method="POST" action="<?= htmlspecialchars(app_route("usuario", "store"), ENT_QUOTES, "UTF-8") ?>" class="row g-2">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>">
            <div class="col-md-3"><input name="nombre_usuario" class="form-control" placeholder="Nombre completo" required maxlength="150"></div>
            <div class="col-md-2"><input name="usuario" class="form-control" placeholder="Usuario" required maxlength="50"></div>
            <div class="col-md-2"><input name="password" type="password" class="form-control" placeholder="Contraseña" required minlength="8"></div>
            <div class="col-md-3"><select name="rol" class="form-select" required><?php foreach ($roles as $rol): ?><option value="<?= htmlspecialchars($rol) ?>"><?= htmlspecialchars(ucfirst($rol)) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Crear</button></div>
        </form>
    </div></div>
    <div class="table-wrap"><table class="table table-striped table-hover align-middle"><thead><tr><th>Nombre</th><th>Usuario</th><th>Rol</th><th>Estado</th><th>Nueva contraseña</th><th>Acciones</th></tr></thead><tbody>
    <?php foreach ($usuarios as $usuario): ?><tr>
        <?php $formId = "usuario-actualizar-" . (int) $usuario["id_usuario"]; ?>
        <form id="<?= $formId ?>" method="POST" action="<?= htmlspecialchars(app_route("usuario", "update"), ENT_QUOTES, "UTF-8") ?>"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>"><input type="hidden" name="id_usuario" value="<?= (int) $usuario["id_usuario"] ?>"></form>
        <td><input form="<?= $formId ?>" name="nombre_usuario" value="<?= htmlspecialchars($usuario["nombre_usuario"]) ?>" class="form-control form-control-sm" required></td>
        <td><input form="<?= $formId ?>" name="usuario" value="<?= htmlspecialchars($usuario["usuario"]) ?>" class="form-control form-control-sm" required></td>
        <td><select form="<?= $formId ?>" name="rol" class="form-select form-select-sm"><?php foreach ($roles as $rol): ?><option value="<?= htmlspecialchars($rol) ?>" <?= $usuario["rol"] === $rol ? "selected" : "" ?>><?= htmlspecialchars(ucfirst($rol)) ?></option><?php endforeach; ?></select></td>
        <td><?= (int) $usuario["estado"] === 1 ? "Activo" : "Inactivo" ?></td>
        <td><input form="<?= $formId ?>" name="password" type="password" class="form-control form-control-sm" placeholder="Nueva contraseña"></td>
        <td><button form="<?= $formId ?>" class="btn btn-sm btn-outline-primary">Guardar</button>
            <form method="POST" action="<?= htmlspecialchars(app_route("usuario", "toggle"), ENT_QUOTES, "UTF-8") ?>" class="d-inline" onsubmit="return confirm('¿Cambiar estado de esta cuenta?');"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>"><input type="hidden" name="id_usuario" value="<?= (int) $usuario["id_usuario"] ?>"><input type="hidden" name="estado" value="<?= (int) $usuario["estado"] ?>"><button class="btn btn-sm btn-outline-secondary"><?= (int) $usuario["estado"] === 1 ? "Desactivar" : "Activar" ?></button></form>
            <form method="POST" action="<?= htmlspecialchars(app_route("usuario", "delete"), ENT_QUOTES, "UTF-8") ?>" class="d-inline" onsubmit="return confirm('¿Desactivar definitivamente esta cuenta?');"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>"><input type="hidden" name="id_usuario" value="<?= (int) $usuario["id_usuario"] ?>"><button class="btn btn-sm btn-outline-danger">Eliminar</button></form>
        </td>
    </tr><?php endforeach; ?></tbody></table></div>
</div>
