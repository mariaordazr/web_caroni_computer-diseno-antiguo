<?php include ROOT_PATH . 'app/views/admin/templates/filterbar.php'; ?>
<section class="content-panel product-view-container view-list"> 
    <?php if (!empty($users)): ?>

        <div class="table-list-ml">
            <?php foreach($users as $user): ?>
                <?php
                    $isSuperAdmin = !empty($user['is_super_admin']) && $user['is_super_admin'] == 1;
                    $isCurrentUser = $_SESSION['idUser'] == $user['id_user'];
                    $isCurrentUserAdmin = $userInfo['is_super_admin'] == 1;
                    
                    // Regla de Edición:
                    // Se puede editar si:
                    // 1. NO es super admin
                    // 2. ES super admin Y SOY YO (el usuario logueado)
                    $canEdit = !$isSuperAdmin || $isCurrentUserAdmin;
                    
                    // Regla de Eliminación:
                    // Se puede eliminar si:
                    // 1. NO es super admin
                    $canDelete = !$isSuperAdmin && !$isCurrentUser;
                ?>
                <div class="table-item-ml">
                    <?php if ($canEdit): ?>
                        <a href="/admin/users/edit/<?php echo $user['id_user']; ?>">
                    <?php endif; ?>
                        <div class="product-item-ml__image">
                            <?php 
                                // 1. Si el campo avatar en la BD está vacío, asignamos la imagen 'default.jpg'
                                $originalFile = !empty($user['avatar']) ? $user['avatar'] : 'default.jpg'; 

                                // 2. Control de la versión optimizada: Si es la foto por defecto, no buscamos el prefijo 'small_'
                                if ($originalFile === 'default.jpg') {
                                    $smallFile = 'default.jpg';
                                } else {
                                    $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); 
                                    $smallFile = 'small_' . $nameWithoutExt . '.webp'; 
                                }
                            ?>
                            <picture>
                                <source srcset="/assets/uploads/users/<?php echo rawurlencode($smallFile); ?>">
                                <img src="/assets/uploads/users/<?php echo rawurlencode($originalFile); ?>"
                                    alt="Avatar de <?php echo htmlspecialchars($user['name']); ?>"
                                    loading="lazy">
                            </picture>
                        </div>
                        <div class="table-item-ml__info">
                            <p class="info__title">
                                Usuario: <?php echo htmlspecialchars($user['username']); ?>
                                
                                <?php if (!empty($user['role'])):
                                    if ($user['role'] == 'admin'):
                                        $roleLabel = 'Administrador';
                                    elseif ($user['role'] == 'seller'):
                                        $roleLabel = 'Vendedor';
                                    else:
                                        $roleLabel = 'Técnico';
                                    endif; ?>
                                    <span style="color: #007bff; font-weight: 600; font-size: 0.9em;"> (<?php echo htmlspecialchars($roleLabel); ?>)</span>
                                <?php endif; ?>
                                </p>
                            <p class="info__meta">
                                Correo: <?php echo htmlspecialchars($user['email']); ?>
                            </p>
                        </div>
                        
                        <!-- <div class="table-item-ml__create">
                            <span class="item__cu">Creado: <?php echo htmlspecialchars(date('d/m/Y H:m:s', strtotime($user['created_at']))); ?></span>    
                        </div> -->
                        <div class="table-item-ml__update">
                            <span class="item__cu">Creado: <?php echo htmlspecialchars(date('d/m/Y H:m', strtotime($user['created_at']))); ?></span>    
                            <span class="item__cu">Modificado: <?php echo htmlspecialchars(date('d/m/Y H:m', strtotime($user['updated_at']))); ?></span>
                        </div>
                    <?php if ($canEdit): ?>
                        </a>
                    <?php endif; ?>
                    <div class="table-item-ml__actions">
                        <div class="actions-menu-container">
                            <?php if ($canEdit): ?>
                                <button class="actions-menu-toggle"><i class='bx bx-dots-vertical-rounded'></i></button>
                                <div class="actions-dropdown">
                                    <a href="/admin/users/edit/<?php echo $user['id_user']; ?>">Editar</a>
                                    <?php if ($canDelete): ?>
                                        <a href="/admin/users/delete/<?php echo $user['id_user']; ?>" class="btn-delete danger">Eliminar</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="generic-grid-container">
            <?php foreach($users as $user): ?>
                <?php
                    // Repetimos la lógica para la vista de cuadrícula
                    $isSuperAdmin = !empty($user['is_super_admin']) && $user['is_super_admin'] == 1;
                    $isCurrentUser = $_SESSION['idUser'] == $user['id_user'];
                    $canEdit = !$isSuperAdmin || ($isSuperAdmin && $isCurrentUser);
                    $canDelete = !$isSuperAdmin;
                ?>
                <div class="grid-item-card">
                    <?php if ($canEdit): ?>
                        <a href="/admin/users/edit/<?php echo $user['id_user']; ?>">
                    <?php endif; ?>
                        <div class="grid-item-card__image">
                            <?php 
                                // 1. Si el campo avatar en la BD está vacío, asignamos la imagen 'default.jpg'
                                $originalFile = !empty($user['avatar']) ? $user['avatar'] : 'default.jpg'; 

                                // 2. Control de la versión optimizada: Si es la foto por defecto, no buscamos el prefijo 'small_'
                                if ($originalFile === 'default.jpg') {
                                    $smallFile = 'default.jpg';
                                } else {
                                    $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); 
                                    $smallFile = 'small_' . $nameWithoutExt . '.webp'; 
                                }
                            ?>
                            <picture>
                                <source srcset="/assets/uploads/users/<?php echo rawurlencode($smallFile); ?>">
                                <img src="/assets/uploads/users/<?php echo rawurlencode($originalFile); ?>"
                                    alt="Avatar de <?php echo htmlspecialchars($user['name']); ?>"
                                    loading="lazy">
                            </picture>
                        </div>
                        <div class="grid-item-card__content">
                            <p class="grid-item-card__title">
                                <?php echo htmlspecialchars($user['name']); ?>
                                <?php if (!empty($user['role'])): ?>
                                    <span style="color: #007bff; font-weight: 600; font-size: 0.9em;"> (<?php echo htmlspecialchars($user['role']); ?>)</span>
                                <?php endif; ?>
                                </p>
                            <p class="grid-item-card__subtitle"><?php echo htmlspecialchars($user['username']); ?></p>
                        </div>
                    <?php if ($canEdit): ?>
                        </a>
                    <?php endif; ?>
                    <div class="grid-item-card__actions">
                        <div class="actions-menu-container">
                            <?php if ($canEdit): ?>
                                <button class="actions-menu-toggle"><i class='bx bx-dots-vertical-rounded'></i></button>
                                <div class="actions-dropdown">
                                    <a href="/admin/users/edit/<?php echo $user['id_user']; ?>">Editar</a>
                                    <?php if ($canDelete && !$isCurrentUser): ?>
                                        <a href="/admin/users/delete/<?php echo $user['id_user']; ?>" class="btn-delete danger">Eliminar</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-results-message">
            <i class='bx bx-search-alt'></i>
            <h2>No se encontraron usuarios</h2>
            <p>Prueba a cambiar los filtros o a restablecer la búsqueda.</p>
        </div>
    <?php endif; ?>

<?php include ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>