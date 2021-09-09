<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<input type="hidden" id="csrf_token" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />

<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
	</div>
</div>
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
		<div class="card border-left-primary shadow h-100 py-2">
			<div class="card-body">
			<div class="row no-gutters align-items-center">
				<div class="col mr-2">
				<div class="text-s font-weight-bold text-primary text-uppercase mb-1">
					<a href="<?= base_url('Source/List') ?>">Sources</a>
				</div>
				<div class="h4 mb-0 font-weight-bold text-gray-800"><?= $sourceCount ?></div>
				</div>
				<div class="col-auto">
				<i class="fas fa-database fa-2x"></i>
				</div>
			</div>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-success shadow h-100 py-2">
			<div class="card-body">
			<div class="row no-gutters align-items-center">
				<div class="col mr-2">
				<div class="text-s font-weight-bold text-success text-uppercase mb-1">
					<a class="text-success" href="<?= base_url('Network/List') ?>">Networks You Are In</a>
				</div>
				<div class="h4 mb-0 font-weight-bold text-gray-800"><?= $networksCount ?></div>
				</div>
				<div class="col-auto">
				<i class="fas fa-network-wired fa-2x"></i>
				</div>
			</div>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-info shadow h-100 py-2">
			<div class="card-body">
			<div class="row no-gutters align-items-center">
				<div class="col mr-2">
				<div class="text-s font-weight-bold text-info text-uppercase mb-1">
				<a class="text-info" href="<?= base_url('User/List') ?>">Users</a>
				</div>
				<div class="row no-gutters align-items-center">
					<div class="col-auto">
					<div class="h4 mb-0 mr-3 font-weight-bold text-gray-800"><?= $usersCount ?></div>
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
		</div>
	</div>

	<div class="col-xl-3 col-md-6 mb-4">
		<div class="card border-left-warning shadow h-100 py-2">
			<div class="card-body">
			<div class="row no-gutters align-items-center">
				<div class="col mr-2">
				<div class="text-s font-weight-bold text-warning text-uppercase mb-1">
					<a class="text-warning" href="<?= base_url('NetworkRequest/List') ?>">Pending Network Requests</a>
				</div>
				<div class="h4 mb-0 font-weight-bold text-gray-800"><?= $networkRequestCount?></div>
				</div>
				<div class="col-auto">
				<i class="fas fa-comments fa-2x"></i>
				</div>
			</div>
			</div>
		</div>
	</div>

</div>

<div class="row">
	<div class="col-xl-8 col-lg-7">
		<div class="card shadow mb-4">
			<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
			<h6 class="m-0 font-weight-bold text-primary">Record Count per Source </h6>
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
				<div class="card shadow mb-1">
					<!-- Card Header - Dropdown -->
					<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
						<h6 class="m-0 font-weight-bold text-primary">Disk Space Usage</h6>
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
					<h6 class="m-0 font-weight-bold text-primary">Service Status</h6>
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
							<div class="col-4">Service</div>
							<div class="col-2">
							<?php if($serviceStatus): ?>
									<span class="icon text-50">
									<i class="fas fa-check text-success"></i>
									</span>
								<?php else: ?>
									<span class="icon text-50">
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
							<div class="col-4">OpenID Provider</div>
							<div class="col-2">
								<?php if($keycloakStatus): ?>
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
var rchart = document.getElementById('recordsrc_chart');
var dchart = document.getElementById('disk_chart');

$(document).ready(function(){
	var csrf_token = $('#csrf_token').val();
	var csrf_token_name = $('#csrf_token').prop('name');
	$.ajax({
		url: baseurl + "AjaxApi/getSourceCounts",
		type: 'post',
		data:  csrf_token_name + '=' + csrf_token,
		success: function(result){
			var recordChart = new Chart(rchart.getContext('2d'), {
			type: 'bar',
			data: {
				labels: [<?= $sourceNames ?>],
				datasets: [{
					label: 'Records',
					backgroundColor: '#36a2eb',
					borderColor: 'rgb(255, 99, 132)',
					data: JSON.parse(result)
				}]
			},
			options: {
				scales: {
					yAxes: [{
						ticks: {
							beginAtZero: true
						}
					}]
				}
			}
			});
		rchart.style.display = 'block';
		document.getElementById('records_spinner').style.display = 'none';
		}
	});

	var chart = new Chart(dchart.getContext('2d'), {
	type: 'doughnut',
	data: {
		labels: ['Available', 'Used'],
		datasets: [{
			label: 'Disk Space Usage',
			data: [ <?= round(disk_free_space('/') / 1073741824, 2) ?>, <?= round(disk_total_space('/') / 1073741824, 2) ?>],
			backgroundColor:["lightgreen", "orange"]
		}]
	},
	options:{}
	});

});

</script>
<?= $this->endSection() ?>
