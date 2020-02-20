<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>	
	</div>	
</div>
<hr>
<div class="row">

<div class="col-xl-3 col-md-6 mb-4">
  <div class="card border-left-primary shadow h-100 py-2">
	<div class="card-body">
	  <div class="row no-gutters align-items-center">
		<div class="col mr-2">
		  <div class="text-s font-weight-bold text-primary text-uppercase mb-1">Sourecs</div>
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
		  <div class="text-s font-weight-bold text-success text-uppercase mb-1">Networks You Are In</div>
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
		  <div class="text-s font-weight-bold text-info text-uppercase mb-1">Users</div>
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
		  <div class="text-s font-weight-bold text-warning text-uppercase mb-1">Pending Network Requests</div>
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
	  <div class="chart-container">
		<canvas id="recordsrc_chart"></canvas>
	  </div>
	</div>
  </div>
</div>

<!-- Pie Chart -->
<div class="col-xl-4 col-lg-5">
	<div class="row m-0">
		<div class="col">
			<div class="card shadow mb-1">
				<!-- Card Header - Dropdown -->
				<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
				<h6 class="m-0 font-weight-bold text-primary">Disk Space Usage</h6>
				</div>
				<!-- Card Body -->
				<div class="card-body">
				<div class="chart-pie pt-4 pb-2">
					<canvas id="disk_chart"></canvas>
				</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row m-0">
		<div class="col">
			<div class="card shadow mb-4">
				<!-- Card Header - Dropdown -->
				<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
				<h6 class="m-0 font-weight-bold text-primary">Service Status</h6>
				</div>
				<!-- Card Body -->
				<div class="card-body">
					<div class="row mb-1">
						<div class="col-4">Elastic Search</div>
						<div class="col-8">
							<?php if($elasticStatus): ?>
							<a href="#" class="btn btn-success btn-icon-split">
								<span class="icon text-white-50">
								<i class="fas fa-check"></i>
								</span>
								<span class="text">Running</span>
							</a>
							<?php else: ?>
							<a href="#" class="btn btn-secondary btn-icon-split">
								<span class="icon text-white-50">
								<i class="fas fa-cross"></i>
								</span>
								<span class="text">Stopped</span>
							</a>
							<?php endif ?>
						</div>
					</div>
					<div class="row mb-1">
						<div class="col-4">Neo4J</div>
						<div class="col-8">
						<?php if($neo4jStatus): ?>
							<a href="#" class="btn btn-success btn-icon-split">
								<span class="icon text-white-50">
								<i class="fas fa-check"></i>
								</span>
								<span class="text">Running</span>
							</a>
							<?php else: ?>
							<a href="#" class="btn btn-secondary btn-icon-split">
								<span class="icon text-white-50">
								<i class="fas fa-cross"></i>
								</span>
								<span class="text">Stopped</span>
							</a>
							<?php endif ?> 
						</div>
					</div> 
					<div class="row mb-1">
						<div class="col-4">KeyCloak</div>
						<div class="col-8">					                                                                                                                                                                          
							<?php if($keycloakStatus): ?>
							<a href="#" class="btn btn-success btn-icon-split">
								<span class="icon text-white-50">
								<i class="fas fa-check"></i>
								</span>
								<span class="text">Running</span>
							</a>
							<?php else: ?>
							<a href="#" class="btn btn-secondary btn-icon-split">
								<span class="icon text-white-50">
								<i class="fas fa-cross"></i>
								</span>
								<span class="text">Stopped</span>
							</a>
							<?php endif ?> 
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
var rchart = document.getElementById('recordsrc_chart').getContext('2d');
var dchart = document.getElementById('disk_chart').getContext('2d');
$(document).ready(function(){
	var recordChart = new Chart(rchart, {
	type: 'bar',
	data: {
		labels: [<?= $sourceNames ?>],
		datasets: [{
			label: 'Records',
			backgroundColor: '#36a2eb',
			borderColor: 'rgb(255, 99, 132)',
			data: [<?= $sourceCounts ?>]
		}]
	},
	options: {}
	});

	var chart = new Chart(dchart, {
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