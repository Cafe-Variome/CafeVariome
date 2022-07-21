<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col">
        <h2><?= $title ?></h2>
    </div>
</div>
<hr>
<?php if ($statusMessage) : ?>
    <div class="row">
        <div class="col">
            <div class="alert alert-<?= $statusMessageType ?>">
                <?php echo $statusMessage ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<table class="table table-bordered table-striped table-hover" id="sourcestable">
    <thead>
        <tr>
            <th>Name</th>
            <th>Record Count</th>
            <th>Assigned Group(s)</th>
            <th>Status</th>
            <th>Quick Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sources as $source) : ?>
            <td><?php echo $source['name']; ?></td>
            <td><?= $source['record_count']; ?></td>
            <td>
                <?php
                if (isset($source_network_groups)) :
                    if (array_key_exists($source['source_id'], $source_network_groups)) :
                        foreach ($source_network_groups[$source['source_id']] as $group) :
                            echo $group['description'] . " (Network:" . $group['network_name'] . ")<br />";
                        endforeach;
                    else :
                        echo "No groups assigned";
                    endif;
                else :
                    echo "No groups assigned";
                endif;
                ?>
            </td>
            <td>
                <a class="btn btn-<?php if ($source['status'] == 'online') : ?>success<?php elseif ($source['status'] == 'offline') : ?>danger<?php endif; ?> text-white font-weight-bold" data-placement="top" title="Edit source status in Quick Actions -> Editor -> Edit Source -> Status">
                    <?php if ($source['status'] == 'online') : ?>Online<?php elseif ($source['status'] == 'offline') : ?>Offline<?php endif; ?>
                </a>
            </td>
            <td>
                <a class="btn btn-primary text-white font-weight-bold" data-toggle="modal" data-target="#uploadModal" data-id="<?= $source['source_id'] ?>" data-placement="top" title="Upload/import files">
                    <i class="fa fa-folder"></i> File Manager
                </a>
                <a class="btn btn-info text-white font-weight-bold" data-toggle="modal" data-target="#indicesModal" data-placement="top" data-id="<?= $source['source_id'] ?>" data-srcname="<?= $source['name']; ?>" title="View Indices">
                    <i class="fa fa-search"></i> Indices
                </a>
                <a class="btn btn-warning text-white font-weight-bold" data-toggle="modal" data-target="#sourcesModal" data-placement="top" data-id="<?= $source['source_id'] ?>" data-srcname="<?= $source['name']; ?>" title="Edit source">
                    <i class="fa fa-edit"></i> Editor
                </a>
            </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div id="sourceDisplay"></div>

<br />

<div class="row">
    <div class="col">
        <a href="<?php echo base_url($controllerName . '/Create') ?>" class="btn btn-success bg-gradient-success">
            <i class="fa fa-plus"></i> Create a Source
        </a>
    </div>
</div>

<br />
<!-- FILE MANAGER MODAL -->
<div id="uploadModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" align="center" id="uploadModalTitle"><i class="fa fa-folder-open"></i> File Manager</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table>
                    <tr>
                        <td>
                            <a id="bulkImport" class="btn btn-small btn-primary text-white font-weight-bold">
                                <i class="fa fa-file-upload"></i><br/>Spreadsheet Files
                            </a>
                        </td>
                        <td>
                            <a id="phenoPacketsImport" class="btn btn-small btn-primary text-white font-weight-bold">
                                <i class="fa fa-file-upload"></i><br/>PhenoPacket Files
                            </a>
                        </td>
                        <td>
                            <a id="VCFImport" class="btn btn-small btn-primary text-white font-weight-bold">
                                <i class="fa fa-file-upload"></i><br/>VCF Files
                            </a>
                        </td>
                        <td>
                            <a id="importModal" class="btn btn-small btn-success text-white font-weight-bold" data-toggle="tooltip" title="Import files from local server using an absolute path.">
                                <i class="fa fa-file-import"></i><br/> Import Files
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- INDICES MODAL -->
<div id="indicesModal" class="modal fade" style="justify-content: center;align-items:center" tabindex="-1" role="dialog" aria-labelledby="indicesModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" align="center" id="indicesModalTitle"><i class="fa fa-eye"></i>View Indices</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table>
                    <tr>
                        <td>
                            <a id="ESIndex" class="btn btn-info text-white font-weight-bold" data-id="<?= $source['source_id'] ?>" href="<?php echo base_url($controllerName . '/Elasticsearch') . "/" . $source['source_id']; ?>" data-toggle="tooltip" data-placement="top" title="Elasticsearch Index"><i class="fa fa-search text-light"></i>ES Index
                            </a>
                        </td>
                        <td>
                            <a id="NeoIndex" class="btn btn-warning text-white font-weight-bold" data-id="<?= $source['source_id'] ?>" href="<?php echo base_url($controllerName . '/Neo4J') . "/" . $source['source_id']; ?>" data-toggle="tooltip" data-placement="top" title="Neo4J Index"> <i class="fa fa-project-diagram text-light"></i> Neo4J Index
                            </a>
                        </td>
                        <td>
                            <a id="UIIndex" class="btn btn-secondary text-white font-weight-bold" data-id="<?= $source['source_id'] ?>" href="<?php echo base_url($controllerName . '/UserInterface') . "/" . $source['source_id']; ?>" data-toggle="tooltip" data-placement="top" title="User Interface Index"> <i class="fa fa-desktop text-light"></i> UI Index
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- SOURCE ACTIONS/EDITOR MODAL -->
<div id="sourcesModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="sourcesModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" align="center" id="sourcesModalTitle"><i class="fa fa-edit"></i>Source Actions</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col">
                        <a id="srcValues" class="btn btn-small btn-info">
                            <i class="fa fa-database text-light"></i> View Data Attributes and Values
                        </a>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col">
                        <a id="srcEdit" class="btn btn-warning">
                            <i class="fa fa-edit text-light"></i>Edit Source
                        </a>
                        <a id="srcDelete" class="btn btn-danger">
                            <i class="fa fa-trash text-light"></i>Delete Source
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<style>
    .modal-dialog {
        height: 100vh !important;
        display: flex;
    }

    .modal-content {
        margin: auto !important;
        height: fit-content !important;
    }
</style>
<?= $this->endSection() ?>