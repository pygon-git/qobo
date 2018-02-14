<?php
use Cake\Core\Configure;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory($this);

echo $this->Html->css('Qobo/Utils./plugins/datatables/css/dataTables.bootstrap.min', ['block' => 'css']);

echo $this->Html->script(
    [
        'Qobo/Utils./plugins/datatables/datatables.min',
        'Qobo/Utils./plugins/datatables/js/dataTables.bootstrap.min'
    ],
    ['block' => 'scriptBottom']
);

echo $this->Html->scriptBlock(
    '$(".table-datatable").DataTable({
        stateSave:true,
        paging:true,
        searching:true
    });',
    ['block' => 'scriptBottom']
);

?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __('Users');?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
                <div class="btn-group btn-group-sm" role="group">
                <?= $this->Html->link(
                    '<i class="fa fa-plus"></i> ' . __('Add'),
                    ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'add'],
                    ['escape' => false, 'title' => __('Add'), 'class' => 'btn btn-default']
                ); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="box box-solid">
        <div class="box-body">
            <table class="table table-hover table-condensed table-vertical-align table-datatable">
                <thead>
                    <tr>
                <th><?= __('Username') ?></th>
                <th><?= __('Email') ?></th>
                <th><?= __('First Name') ?></th>
                <th><?= __('Last Name') ?></th>
                <th><?= __('Gender') ?></th>
                <th><?= __('Birthdate') ?></th>
                <th><?= __('Active') ?></th>
                <th><?= __('Created') ?></th>
                <th class="actions"><?= __d('Users', 'Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($Users as $user) : ?>
                    <tr>
                        <td><?= h($user->username) ?></td>
                        <td><?= h($user->email) ?></td>
                        <td><?= h($user->first_name) ?></td>
                        <td><?= h($user->last_name) ?></td>
                        <td><?php
                            $definition = [
                                'name' => 'gender',
                                'type' => 'list(genders)',
                                'required' => false
                            ];
                            echo $fhf->renderValue('Users', 'gender', $user, ['fieldDefinitions' => $definition]);
                        ?></td>
                        <td><?= $user->has('birthdate') ? $user->birthdate->i18nFormat('yyyy-MM-dd') : '' ?></td>
                        <td><?= $user->active ? 'Yes' : 'No' ?></td>
                        <td><?= h($user->created->i18nFormat('yyyy-MM-dd hh:mm:ss')) ?></td>
                        <td class="actions">
                            <div class="btn-group btn-group-xs" role="group">
                            <?= $this->Html->link(
                                '<i class="fa fa-eye"></i>',
                                ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'view', $user->id],
                                ['title' => __('View'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                            ); ?>
                            <?= $this->Html->link(
                                '<i class="fa fa-pencil"></i>',
                                ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'edit', $user->id],
                                ['title' => __('Edit'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                            ); ?>
                            <?= $this->Html->link(
                                '<i class="fa fa-lock"></i>',
                                ['action' => 'change-user-password', $user->id],
                                ['title' => __('Change User Password'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                            ) ?>
                            <?= $this->Form->postLink(
                                '<i class="fa fa-trash"></i>',
                                ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'delete', $user->id],
                                [
                                    'confirm' => __('Are you sure you want to delete # {0}?', $user->id),
                                    'title' => __('Delete'),
                                    'class' => 'btn btn-default btn-sm',
                                    'escape' => false
                                ]
                            ) ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
