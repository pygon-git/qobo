<nav class="navbar navbar-static-top" role="navigation">
    <?php if (!empty($user)) : ?>
    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </a>
    <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
            <li><?= $this->element('aside/form') ?></li>
            <!-- User Account: style can be found in dropdown.less -->
            <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <?= $this->Html->tag('img', false, ['src' => $user['image_src'], 'class' => 'user-image']) ?>
                    <span class="hidden-xs"><?= $user['name']; ?></span>
                </a>
                <ul class="dropdown-menu">
                    <!-- User image -->
                    <li class="user-header">
                        <?= $this->Html->tag('img', false, ['src' => $user['image_src'], 'class' => 'img-circle']) ?>
                        <p>
                            <?= $user['name']; ?>
                            <small><?= __d('cake', 'Member since') ?> <?= $user['created']->i18nFormat('LLLL yyyy') ?></small>
                        </p>
                    </li>
                    <!-- Menu Footer-->
                    <li class="user-footer">
                        <div class="pull-left">
                            <?= $this->Html->link(
                                __d('cake', 'Profile'),
                                '/users/profile',
                                ['class' => 'btn btn-default btn-flat']
                            ); ?>
                        </div>
                        <div class="pull-right">
                            <?= $this->Html->link(
                                __d('cake', 'Sign out'),
                                '/users/logout',
                                ['class' => 'btn btn-default btn-flat']
                            ); ?>
                        </div>
                    </li>
                </ul>
            </li>
            <!-- Control Sidebar Toggle Button -->
            <li>
                <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
            </li>
        </ul>
    </div>
    <?php endif; ?>
</nav>
