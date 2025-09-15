<li id="dropdown-user" class="dropdown">
    <a href="#" data-toggle="dropdown" class="dropdown-toggle text-right">
        <span class="pull-right">
            <img class="img-circle img-user media-object" src="img/av1.png" alt="Profile Picture">
        </span>
        <div data-user="<?php print($app->user_id); ?>" class="username hidden-xs"><?php print($app->name); ?></div>
    </a>
    <div class="dropdown-menu dropdown-menu-md dropdown-menu-right with-arrow panel-default">

        <!-- User dropdown menu -->
        <ul class="head-list">
            <li>
                <a href="javascript:void(0)">
                    <i class="fa fa-user fa-fw fa-lg"></i> Mi Perfil - <?php print($app->rol_name) ?>
                </a>
            </li>
        </ul>

        <!-- Dropdown footer -->
        <div class="pad-all text-right">
            <a href="index.php?module=logout" class="btn btn-primary">
                <i class="fa fa-sign-out fa-fw"></i> Logout
            </a>
        </div>
    </div>
</li>