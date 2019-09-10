<?= $this->extend('layout\master') ?>
<?= $this->section('content') ?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
  </ol>
</nav>

<div class="row">
    <div class="col">
        <h4>Data</h4>
    <div>
</div>
<hr/>
<div class="row text-center">
    <div class="col">
        <div class="row">
            <div class="col">
                <a href="<?= base_url("source") ?>"><img src="<?= base_url(IMAGES."cafevariome/dashboard/source.png") ?>"></a>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <a href="<?= base_url("source") ?>">Sources</a>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="row">
            <div class="col"></div>
        </div>
        <div class="row">
            <div class="col"></div>
        </div>
    </div>
    <div class="col">
        <div class="row">
            <div class="col"></div>
        </div>
        <div class="row">
            <div class="col"></div>
        </div>        
    </div>
</div>
<hr/>

<div class="row">
    <div class="col">
        <h4>Access Control</h4>
    <div>
</div>
<hr/>
<div class="row text-center">
    <div class="col">
        <div class="row">
            <div class="col">
                <a href="<?= base_url("admin/user") ?>"><img src="<?= base_url(IMAGES."cafevariome/dashboard/user.png") ?>"></a>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <a href="<?= base_url("admin/user") ?>">Users</a>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="row">
            <div class="col">
                <a href="<?= base_url("networkgroup") ?>"><img src="<?= base_url(IMAGES."cafevariome/dashboard/group.png") ?>"></a>           
            </div>
        </div>
        <div class="row">
            <div class="col">
                <a href="<?= base_url("networkgroup") ?>">Ntework Groups</a>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="row">
            <div class="col"></div>
        </div>
        <div class="row">
            <div class="col"></div>
        </div>        
    </div>
</div>
<hr/>
<div class="row">
    <div class="col">
        <h4>Settings</h4>
    <div>
</div>
<hr/>

<div class="row text-center">
    <div class="col">
        <div class="row">
            <div class="col">
            </div>
        </div>
        <div class="row">
            <div class="col">
            </div>
        </div>
    </div>
    <div class="col">
        <div class="row">
            <div class="col">
            </div>
        </div>
        <div class="row">
            <div class="col">
            </div>
        </div>
    </div>
    <div class="col">
        <div class="row">
            <div class="col">
                <a href="<?= base_url("network") ?>"><img src="<?= base_url(IMAGES."cafevariome/dashboard/network.png") ?>"></a>           
            </div>
        </div>
        <div class="row">
            <div class="col">
                <a href="<?= base_url("network") ?>">Networks</a>
            </div>
        </div>        
    </div>
</div>
<hr/>
<?= $this->endSection() ?>