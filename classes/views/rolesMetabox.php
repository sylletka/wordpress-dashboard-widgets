<label for="enable_roles_limit">
<input name="enable_roles_limit" type=hidden value=0>
<input id="enable_roles_limit" name="enable_roles_limit" type=checkbox value=1' <?php echo $limit_checked ?>>
<?php _e( 'Display this widget only to these roles:', 'wordpress-dashboard-widgets' ); ?>
</label>
<ul id="enabled_roles_list">
<?php foreach ( $roles_keys as $role ): ?>
    <?php $checked = ( array_key_exists( $role, $enabled_roles ) &&  $enabled_roles[ $role ]) ? ' checked' : ''; ?>
    <li>
        <label for="enabled_roles_<?php echo $role ?>">
        <input name="enabled_roles[<?php echo $role ?>]" type=hidden value=0>
        <input id="enabled_roles_<?php echo $role ?>" class="enabled_roles_checkbox" name="enabled_roles[<?php echo $role ?>]" type=checkbox value=1'<?php echo $checked . $disabled ?>>
            <?php echo $roles[ $role ][ 'name' ];?>
        </label>
    </li>
<?php endforeach ?>
</ul>

