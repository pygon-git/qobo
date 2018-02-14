<?php
use RolesCapabilities\Access\AccessFactory;

$accessFactory = new AccessFactory();

$url = ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'saveSearch'];

if ($accessFactory->hasAccess($url, $user)) : ?>
    <?php
    echo $this->Form->label(__('Save search'));

    echo $this->Form->create(null, [
        'class' => 'save-search-form',
        'url' => [
            'plugin' => $this->request->plugin,
            'controller' => $this->request->controller,
            'action' => ($isEditable ? 'edit': 'save') . '-search',
            $preSaveId,
            $isEditable ? $savedSearch->id : null
        ]
    ]); ?>
    <div class="input-group">
        <?= $this->Form->hidden('type', [
            'required' => true,
            'value' => 'criteria'
        ]); ?>
        <?= $this->Form->input('name', [
            'label' => false,
            'class' => 'form-control input-sm',
            'placeholder' => 'Save criteria name',
            'required' => true,
            'value' => $isEditable ? $savedSearch->name : ''
        ]); ?>
        <span class="input-group-btn">
            <?= $this->Form->button(
                '<i class="fa fa-floppy-o"></i>',
                ['class' => 'btn btn-sm btn-primary']
            ) ?>
        </span>
    </div>
    <?= $this->Form->end(); ?>
<?php endif; ?>