<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col">
        <h2><?php echo $first_name; ?> <?= $title ?></h2>
    </div>
</div>
<hr>
<div class="row">
    <div class="col">
        <table class="table table-bordered table-striped table-hover" id="userdetailstable" style="width:40%;">

            <tr>
                <th>ID</th>
                <td><?php echo $id; ?></td>
            </tr>
            <th>Full Name</th>
            <td><?php echo $first_name . " " . $last_name; ?></td>

            </tr>
            <th>Email</th>
            <td><?php echo $uemail; ?></td>
            </tr>
            <th>Company</th>
            <td><?php echo $company; ?></td>
            </tr>
            <th>Phone</th>
            <td><?php echo $phone; ?></td>
            </tr>
            <th>Remote</th>
            <td><?php if ($remote) {
                    echo 'Remote User';
                } else {
                    echo 'Local User';
                } ?></td>
            </tr>
            <th>Network Groups</th>
            <td>
                <?php if (isset($users_groups)) : ?>
                    <?php if (array_key_exists($id, $users_groups)) : ?>
                        <?php foreach ($users_groups[$id] as $group) : ?>
                            <?php echo "<i>'" . $group['group_description'] . "' Network Group</i> (Network: " . $group['network_name'] . ");<br/>" ?>
                        <?php endforeach ?>
                    <?php else : echo "None"; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
            </tr>
            <th>Last Login</th>
            <td><?php echo date("H:i:s d-m-Y T", $last_login); ?></td>
            </tr>
            <th>Created On</th>
            <td><?php echo date("H:i:s d-m-Y T", $created_on); ?></td>
            </tr>
            <th>Role</th>
            <td>
                <?php if ($is_admin) {
                    echo 'Admin';
                } else {
                    echo 'User';
                } ?>
            </td>
            </tr>
            <th>IP Address</th>
            <td><?php echo $ip_address; ?></td>
            </tr>
            <th>Status</th>
            <td><?php if ($active) {
                    echo 'Active';
                } else {
                    echo 'Inactive';
                } ?></td>
            </tr>

        </table>
        <hr />
        <a href="<?php echo base_url($controllerName . '/Update') . "/" . $id; ?>" class="btn btn-warning">
            <i class="fa fa-edit"></i>&nbsp;Edit</a>
        <a href="<?php echo base_url($controllerName . '/List'); ?>" class="btn btn-secondary"><i class="fa fa-backward"></i> Go back</a>
        <hr />
    </div>
</div>

<?= $this->endSection() ?>
