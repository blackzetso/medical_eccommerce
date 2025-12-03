<script setup>
	import { onMounted, ref, computed } from 'vue'
    import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
    import { Head } from '@inertiajs/vue3'

	const props = defineProps({
		stats: Object,
		chartData: Object
	})

	// Chart data for revenue
	const series = ref([
		{
			name: 'الإيرادات',
			data: props.chartData?.revenue || [0, 0, 0, 0, 0, 0, 0]
		}
	])

	const chartOptions = ref({
		chart: {
			id: 'revenue-chart',
			toolbar: { show: false }
		},
		xaxis: {
			categories: props.chartData?.dates || []
		},
		yaxis: {
			labels: { formatter: (val) => `${val} ر.س` }
		},
		dataLabels: { enabled: false },
		stroke: { curve: 'smooth' },
		grid: { strokeDashArray: 4 }
	})

	function formatCurrency(amount) {
		return Number(amount || 0).toFixed(2)
	}
</script>
<template>
    <Head title="Dashboard"/>
    <AppLayout>
    <div class="page-content-wrapper border">
		<!-- Title -->
		<div class="row">
			<div class="col-12 mb-3">
				<h1 class="h3 mb-2 mb-sm-0">Dashboard</h1>
			</div>
		</div>

		<!-- Counter boxes START -->
		<div class="row g-4 mb-4">
			<!-- Counter item -->
			<div class="col-md-6 col-xxl-3">
				<div class="card card-body bg-primary bg-opacity-10 p-4 h-100">
					<div class="d-flex justify-content-between align-items-center">
						<!-- Digit -->
						<div>
							<h2 class="mb-0 fw-bold">{{ stats?.total_products || 0 }}</h2>
							<span class="mb-0 h6 fw-light">إجمالي المنتجات</span>
						</div>
						<!-- Icon -->
						<div class="icon-lg rounded-circle bg-primary text-white mb-0"><i class="bi bi-box fa-fw"></i></div>
					</div>
				</div>
			</div>

			<!-- Counter item -->
			<div class="col-md-6 col-xxl-3">
				<div class="card card-body bg-info bg-opacity-10 p-4 h-100">
					<div class="d-flex justify-content-between align-items-center">
						<!-- Digit -->
						<div>
							<h2 class="mb-0 fw-bold">{{ stats?.total_orders || 0 }}</h2>
							<span class="mb-0 h6 fw-light">إجمالي الطلبات</span>
						</div>
						<!-- Icon -->
						<div class="icon-lg rounded-circle bg-info text-white mb-0"><i class="fas fa-shopping-cart fa-fw"></i></div>
					</div>
				</div>
			</div>

			<!-- Counter item -->
			<div class="col-md-6 col-xxl-3">
				<div class="card card-body bg-success bg-opacity-10 p-4 h-100">
				 	<div class="d-flex justify-content-between align-items-center">
						<!-- Digit -->
						<div>
							<h2 class="mb-0 fw-bold">{{ stats?.total_clients || 0 }}</h2>
							<span class="mb-0 h6 fw-light">إجمالي العملاء</span>
						</div>
						<!-- Icon -->
						<div class="icon-lg rounded-circle bg-success text-white mb-0"><i class="fas fa-users fa-fw"></i></div>
					</div>
				</div>
			</div>

			<!-- Counter item -->
			<div class="col-md-6 col-xxl-3">
				<div class="card card-body bg-warning bg-opacity-10 p-4 h-100">
					<div class="d-flex justify-content-between align-items-center">
						<!-- Digit -->
						<div>
							<h2 class="mb-0 fw-bold">{{ formatCurrency(stats?.total_revenue) }}</h2>
							<span class="mb-0 h6 fw-light">إجمالي الإيرادات (ر.س)</span>
						</div>
						<!-- Icon -->
						<div class="icon-lg rounded-circle bg-warning text-white mb-0"><i class="fas fa-money-bill-wave fa-fw"></i></div>
					</div>
				</div>
			</div>
		</div>
		<!-- Counter boxes END -->

		<!-- Chart and Ticket START -->
		<div class="row g-4 mb-4">

			<!-- Chart START -->
			<div class="col-xxl-8">
				<div class="card shadow h-100">

					<!-- Card header -->
					<div class="card-header p-4 border-bottom">
						<h5 class="card-header-title">الإيرادات</h5>
					</div>

					<!-- Card body -->
					<div class="card-body">
						<!-- Apex chart -->
						<div id="ChartPayout"></div>
                        <div id="chart">
                            <apexchart type="bar" height="350" :options="chartOptions" :series="series" />
                        </div>

					</div>
				</div>
			</div>
			<!-- Chart END -->

			<!-- Recent Orders START -->
			<div class="col-xxl-4">
				<div class="card shadow h-100">
					<!-- Card header -->
					<div class="card-header border-bottom d-flex justify-content-between align-items-center p-4">
						<h5 class="card-header-title">الطلبات الأخيرة</h5>
						<a href="#" class="btn btn-link p-0 mb-0">عرض الكل</a>
					</div>

					<!-- Card body START -->
					<div class="card-body p-4">
						<p class="text-muted text-center">لا توجد طلبات حديثة</p>
					</div>
					<!-- Card body END -->
				</div>
			</div>
			<!-- Ticket END -->
		</div>
		<!-- Chart and Ticket END -->

		<!-- Top listed Cards START -->
		<div class="row g-4">

			<!-- Top Products START -->
			<div class="col-lg-6 col-xxl-4">
				<div class="card shadow h-100">

					<!-- Card header -->
					<div class="card-header border-bottom d-flex justify-content-between align-items-center p-4">
						<h5 class="card-header-title">أفضل المنتجات</h5>
						<a href="#" class="btn btn-link p-0 mb-0">عرض الكل</a>
					</div>

					<!-- Card body START -->
					<div class="card-body p-4">
						<p class="text-muted text-center">لا توجد بيانات</p>
					</div>
					<!-- Card body END -->
				</div>
			</div>
			<!-- Top instructors END -->

			<!-- Order Statistics START -->
			<div class="col-lg-6 col-xxl-4">
				<div class="card shadow h-100">
					<!-- Card header -->
					<div class="card-header border-bottom p-4">
						<h5 class="card-header-title">إحصائيات الطلبات</h5>
					</div>

					<!-- Card body START -->
					<div class="card-body p-4">
						<div class="mb-3">
							<h6 class="mb-2">الطلبات المعلقة</h6>
							<h3 class="mb-0">{{ stats?.pending_orders || 0 }}</h3>
						</div>
						<hr>
						<div class="mb-3">
							<h6 class="mb-2">الطلبات المكتملة</h6>
							<h3 class="mb-0">{{ stats?.completed_orders || 0 }}</h3>
						</div>
					</div>
					<!-- Card body END -->
				</div>
			</div>
			<!-- Notice Board END -->


		</div>
		<!-- Top listed Cards END -->

	</div>
    </AppLayout>
</template>
