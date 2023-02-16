<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<input type="hidden" id="csrf_token" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
<h2 class="mt-4">
	<?= $title ?>
</h2>
<hr>
<?php if($networkMsg): ?>
<div class="row">
	<div class="col">
		<div class="alert alert-danger">
			<div class="row">
				<div class="col-9">
					<?php echo $networkMsg ?>
				</div>
				<div class="col-3">
					<span class="fa fa-exclamation-triangle fa-5x"></span>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<?php if($elasticMsg): ?>
<div class="row">
	<div class="col">
		<div class="alert alert-warning">
			<div class="row">
				<div class="col">
					<?php echo $elasticMsg ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<?php if($neo4jMsg): ?>
<div class="row">
	<div class="col">
		<div class="alert alert-warning">
			<div class="row">
				<div class="col">
					<?php echo $neo4jMsg ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<div class="row">
	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card bg-primary text-white shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col me-2">
						<div class="font-weight-bold text-uppercase mb-1">
							<a class="text-white text-decoration-none" href="<?= base_url('Source/List') ?>">Sources</a>
						</div>
						<div class="h4 mb-0 font-weight-bold"><?= $sourceCount ?></div>
					</div>
					<div class="col-auto">
						<i class="fas fa-database fa-2x"></i>
					</div>
				</div>
			</div>
			<div class="card-footer d-flex align-items-center justify-content-between">
				<a class="small text-white stretched-link" href="<?= base_url('Source/List') ?>">View Sources</a>
				<div class="small text-white"><i class="fas fa-angle-right"></i></div>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card bg-success text-white shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col me-2">
						<div class="font-weight-bold text-uppercase mb-1">
							<a class="text-white text-decoration-none" href="<?= base_url('Network/List') ?>">Networks You Are In</a>
						</div>
						<div class="h4 mb-0 font-weight-bold text-gray-800"><?= $networksCount ?></div>
					</div>
					<div class="col-auto">
						<i class="fas fa-network-wired fa-2x"></i>
					</div>
				</div>
			</div>
			<div class="card-footer d-flex align-items-center justify-content-between">
				<a class="small text-white stretched-link" href="<?= base_url('Network/List') ?>">View Networks</a>
				<div class="small text-white"><i class="fas fa-angle-right"></i></div>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card bg-info text-white shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col me-2">
						<div class="font-weight-bold text-uppercase mb-1">
							<a class="text-white text-decoration-none" href="<?= base_url('User/List') ?>">Users</a>
						</div>
						<div class="row no-gutters align-items-center">
							<div class="col-auto">
								<div class="h4 mb-0 me-3 font-weight-bold text-gray-800"><?= $usersCount ?></div>
							</div>
							<div class="col">
							</div>
						</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-users fa-2x"></i>
					</div>
				</div>
			</div>
			<div class="card-footer d-flex align-items-center justify-content-between">
				<a class="small text-white stretched-link" href="<?= base_url('User/List') ?>">View Users</a>
				<div class="small text-white"><i class="fas fa-angle-right"></i></div>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card bg-warning text-white shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col me-2">
						<div class="font-weight-bold text-warning text-uppercase mb-1">
							<a class="text-white text-decoration-none" href="<?= base_url('NetworkRequest/List') ?>">Pending Network Requests</a>
						</div>
						<div class="h4 mb-0 font-weight-bold text-gray-800"><?= $networkRequestCount?></div>
					</div>
					<div class="col-auto">
						<i class="fas fa-comments fa-2x"></i>
					</div>
				</div>
			</div>
			<div class="card-footer d-flex align-items-center justify-content-between">
				<a class="small text-white stretched-link" href="<?= base_url('NetworkRequest/List') ?>">View Pending Network Requests</a>
				<div class="small text-white"><i class="fas fa-angle-right"></i></div>
			</div>
		</div>
	</div>

</div>

<div class="row">
	<div class="col-xl-8 col-lg-7">
		<div class="card shadow mb-4">
			<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
			<h6 class="m-0 font-weight-bold">Record Count per Source </h6>
			</div>
			<div class="card-body">
				<div class="chart-container text-center">
					<canvas id="recordsrc_chart" style="display:none;"></canvas>
					<div class="spinner-grow text-info" role="status" style="width: 5rem; height: 5rem;" id="records_spinner">
						<span class="sr-only">Loading...</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Pie Chart -->
	<div class="col-xl-4 col-lg-5">
		<div class="row">
			<div class="col">
				<div class="card shadow mb-4">
					<!-- Card Header - Dropdown -->
					<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
						<h6 class="m-0 font-weight-bold">Disk Space Usage</h6>
					</div>
					<!-- Card Body -->
					<div class="card-body">
						<div class="chart-pie pt-4 pb-2">
							<div><canvas id="disk_chart"></canvas></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<div class="card shadow">
					<!-- Card Header - Dropdown -->
					<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
					<h6 class="m-0 font-weight-bold">Service Status</h6>
					</div>
					<!-- Card Body -->
					<div class="card-body">
						<div class="row mb-1">
							<div class="col-4">Elastic Search</div>
							<div class="col-2">
								<?php if($elasticStatus): ?>
									<span class="icon text-50">
									<i class="fas fa-check text-success"></i>
									</span>
								<?php else: ?>
									<span class="icon text-50">
									<i class="fas fa-cross text-secondary"></i>
									</span>
								<?php endif ?>
							</div>
							<div class="col-4">
								Service
								(
								<span id="spinner" class="spinner-border spinner-border-sm text-warning" role="status" aria-hidden="true" style="display:none"></span>
								<span id="shutdownService" class="icon text-50" data-toggle="tooltip" data-placement="top" title="Shutdown" onclick="shutdownService();" style="<?= $serviceStatus ? '' : 'display:none' ?>">
									<i class="fa fa-stop text-danger"></i>
								</span>
								<span id="startService"class="icon text-50" data-toggle="tooltip" data-placement="top" title="Start" onclick="startService();" style="<?= $serviceStatus ? 'display:none' : '' ?>">
									<i class="fa fa-play text-primary"></i>
								</span>
								)
							</div>
							<div class="col-2">
							<?php if($serviceStatus): ?>
									<span id="serviceStatus" class="icon text-50">
										<i class="fas fa-check text-success"></i>
									</span>
								<?php else: ?>
									<span id="serviceStatus" class="icon text-50">
										<i class="fas fa-cross text-secondary"></i>
									</span>
								<?php endif ?>
							</div>
						</div>
						<div class="row mb-1">
							<div class="col-4">Neo4J</div>
							<div class="col-2">
							<?php if($neo4jStatus): ?>
									<span class="icon text-50">
										<i class="fas fa-check text-success"></i>
									</span>
								<?php else: ?>
									<span class="icon text-50">
										<i class="fas fa-cross text-secondary"></i>
									</span>
								<?php endif ?>
							</div>
							<div class="col-4">Authenticator</div>
							<div class="col-2">
								<?php if($openIDStatus): ?>
									<span class="icon text-50">
										<i class="fas fa-check text-success"></i>
									</span>
								<?php else: ?>
									<span class="icon text-white-50">
										<i class="fas fa-cross text-secondary"></i>
									</span>
								<?php endif ?>
							</div>
						</div>
						<hr>
						<div class="row mb-1">
							<div class="col"><i class="fas fa-check text-success"></i> : Running </div>
							<div class="col"><i class="fas fa-cross text-secondary"></i> : Inactive </div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function() {
		loadCharts([<?= $sourceNames ?>], <?= round((disk_total_space(FCPATH) / 1073741824) - disk_free_space(FCPATH) / 1073741824, 2) ?>, <?= round(disk_free_space(FCPATH) / 1073741824, 2) ?>);
	}, false);
</script>
<?= $this->endSection() ?>
