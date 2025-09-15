<li class="dropdown">
    <a id="city-switch" class="lang-selector dropdown-toggle" href="#" data-toggle="dropdown">
        <span class="lang-selected">
            <img class="lang-flag" src="img/logos/logo-<?php print($city[$app->city]['icon']); ?>-mini.png" alt="<?php print($city[$app->city]['name']); ?>">
            <span class="lang-id"><?php print($app->city) ?></span>
            <span class="lang-name"><?php print($city[$app->city]['name']); ?></span>
        </span>
    </a>

    <!-- Language selector menu -->
    <ul class="head-list dropdown-menu with-arrow">
        <li>
            <!-- Murcia -->
            <a href="#" <?php if ($app->city == 'MU') { ?>class="active"<?php } ?>>
                <img class="lang-flag" src="img/logos/logo-muybici-mini.png" alt="Murcia">
                <span class="lang-id">MU</span>
                <span class="lang-name">Murcia</span>
            </a>
        </li>
        <li>
            <!-- Altea -->
            <a href="#" <?php if ($app->city == 'AL') { ?>class="active"<?php } ?>>
                <img class="lang-flag" src="img/logos/logo-bicialtea-mini.png" alt="Altea">
                <span class="lang-id">AL</span>
                <span class="lang-name">Altea</span>
            </a>
        </li>
        <li>
            <!-- Benidorm -->
            <a href="#" <?php if ($app->city == 'BE') { ?>class="active"<?php } ?>>
                <img class="lang-flag" src="img/logos/logo-bicidorm-mini.png" alt="Benidorm">
                <span class="lang-id">BE</span>
                <span class="lang-name">Benidorm</span>
            </a>
        </li>
        <li>
            <!-- Ponferrada -->
            <a href="#" <?php if ($app->city == 'PO') { ?>class="active"<?php } ?>>
                <img class="lang-flag" src="img/logos/logo-ponferrada-mini.png" alt="Ponferrada">
                <span class="lang-id">PO</span>
                <span class="lang-name">Ponferrada</span>
            </a>
        </li>
        <li>
            <!-- Majadahonda -->
            <a href="#" <?php if ($app->city == 'MA') { ?>class="active"<?php } ?>>
                <img class="lang-flag" src="img/logos/logo-majadahonda-mini.png" alt="Majadahonda">
                <span class="lang-id">MA</span>
                <span class="lang-name">Majadahonda</span>
            </a>
        </li>
        <li>
            <!-- Gandia -->
            <a href="#" <?php if ($app->city == 'GA') { ?>class="active"<?php } ?>>
                <img class="lang-flag" src="img/logos/logo-gandia-mini.png" alt="Gandia">
                <span class="lang-id">GA</span>
                <span class="lang-name">Gandia</span>
            </a>
        </li>
        <li>
            <!-- Getafe -->
            <a href="#" <?php if ($app->city == 'GE') { ?>class="active"<?php } ?>>
                <img class="lang-flag" src="img/logos/logo-gbici-mini.png" alt="Gandia">
                <span class="lang-id">GE</span>
                <span class="lang-name">Getafe</span>
            </a>
        </li>
        <li>
            <!-- Vilagarcía -->
            <a href="#" <?php if ($app->city == 'VI') { ?>class="active"<?php } ?>>
                <img class="lang-flag" src="img/logos/logo-vilagarcia-mini.png" alt="Vilagarcía">
                <span class="lang-id">VI</span>
                <span class="lang-name">Vilagarcía</span>
            </a>
        </li>
    </ul>
</li>