<template>
    <Head title="لوحة التحكم" />
    <FrontLayout>
		<nav aria-label="breadcrumb" class="breadcrumb-nav">
			<div class="container">
				<ol class="breadcrumb">
					<li class="breadcrumb-item">
						<Link :href="route('/')">الرئيسية</Link>
					</li>
					<li class="breadcrumb-item">
						<Link :href="route('web.categories')">الأقسام</Link>
					</li>
					<li v-if="category" class="breadcrumb-item active" aria-current="page">
						{{ category.name }}
					</li>
					<li v-else class="breadcrumb-item active" aria-current="page">
						المتجر
					</li>
				</ol>
			</div><!-- نهاية الحاوية -->
		</nav>

        <div class="container account-container custom-account-container">
				<div class="row">
					<div class="sidebar widget widget-dashboard mb-lg-0 mb-3 col-lg-3 order-0">
						<h2 class="text-uppercase">حسابي</h2>
						<ul class="nav nav-tabs list flex-column mb-0" role="tablist">
							<li class="nav-item">
								<a class="nav-link active" id="dashboard-tab" data-toggle="tab" href="#dashboard"
									role="tab" aria-controls="dashboard" aria-selected="true">لوحة التحكم</a>
							</li>

							<li class="nav-item">
								<a class="nav-link" id="order-tab" data-toggle="tab" href="#order" role="tab"
									aria-controls="order" aria-selected="true">الطلبات</a>
							</li>

							<li class="nav-item">
								<a class="nav-link" id="download-tab" data-toggle="tab" href="#download" role="tab"
									aria-controls="download" aria-selected="false">التنزيلات</a>
							</li>

							<li class="nav-item">
								<a class="nav-link" id="address-tab" data-toggle="tab" href="#address" role="tab"
									aria-controls="address" aria-selected="false">العناوين</a>
							</li>

							<li class="nav-item">
								<a class="nav-link" id="edit-tab" data-toggle="tab" href="#edit" role="tab"
									aria-controls="edit" aria-selected="false">تفاصيل الحساب</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="shop-address-tab" data-toggle="tab" href="#shipping" role="tab"
									aria-controls="edit" aria-selected="false">عنوان الشحن</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="wishlist.html">قائمة الرغبات</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="#" @click.prevent="logout">تسجيل الخروج</a>
							</li>
						</ul>
					</div>
					<div class="col-lg-9 order-lg-last order-1 tab-content">
						<div class="tab-pane fade show active" id="dashboard" role="tabpanel">
							<div class="dashboard-content">
								<p>
									مرحباً <strong class="text-dark">Editor</strong> (ليس
									<strong class="text-dark">Editor</strong>؟
									<a href="#" class="btn btn-link" @click.prevent="logout">تسجيل الخروج</a>)
								</p>

								<p>
									من لوحة التحكم في حسابك يمكنك عرض
									<a class="btn btn-link link-to-tab" href="#order">طلباتك الأخيرة</a>،
									إدارة
									<a class="btn btn-link link-to-tab" href="#address">عناوين الشحن والفواتير</a>، و
									<a class="btn btn-link link-to-tab" href="#edit">تعديل كلمة المرور وتفاصيل الحساب.</a>
								</p>

								<div class="mb-4"></div>

								<div class="row row-lg">
									<div class="col-6 col-md-4">
										<div class="feature-box text-center pb-4">
											<a href="#order" class="link-to-tab"><i class="sicon-social-dropbox"></i></a>
											<div class="feature-box-content">
												<h3>الطلبات</h3>
											</div>
										</div>
									</div>

									<div class="col-6 col-md-4">
										<div class="feature-box text-center pb-4">
											<a href="#download" class="link-to-tab"><i
													class="sicon-cloud-download"></i></a>
											<div class=" feature-box-content">
												<h3>التنزيلات</h3>
											</div>
										</div>
									</div>

									<div class="col-6 col-md-4">
										<div class="feature-box text-center pb-4">
											<a href="#address" class="link-to-tab"><i
													class="sicon-location-pin"></i></a>
											<div class="feature-box-content">
												<h3>العناوين</h3>
											</div>
										</div>
									</div>

									<div class="col-6 col-md-4">
										<div class="feature-box text-center pb-4">
											<a href="#edit" class="link-to-tab"><i class="icon-user-2"></i></a>
											<div class="feature-box-content p-0">
												<h3>تفاصيل الحساب</h3>
											</div>
										</div>
									</div>

									<div class="col-6 col-md-4">
										<div class="feature-box text-center pb-4">
											<a href="wishlist.html"><i class="sicon-heart"></i></a>
											<div class="feature-box-content">
												<h3>قائمة الرغبات</h3>
											</div>
										</div>
									</div>

									<div class="col-6 col-md-4">
										<div class="feature-box text-center pb-4">
											<a href="#" @click.prevent="logout"><i class="sicon-logout"></i></a>
											<div class="feature-box-content">
												<h3>تسجيل الخروج</h3>
											</div>
										</div>
									</div>
								</div><!-- End .row -->
							</div>
						</div><!-- End .tab-pane -->

						<div class="tab-pane fade" id="order" role="tabpanel">
							<div class="order-content">
								<h3 class="account-sub-title d-none d-md-block"><i
									class="sicon-social-dropbox align-middle mr-3"></i>الطلبات</h3>
								<div class="order-table-container text-center">
									<table class="table table-order text-left">
										<thead>
											<tr>
												<th class="order-id">رقم الطلب</th>
												<th class="order-date">التاريخ</th>
												<th class="order-status">الحالة</th>
												<th class="order-price">الإجمالي</th>
												<th class="order-action">الإجراءات</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="text-center p-0" colspan="5">
													<p class="mb-5 mt-5">
														لم يتم إجراء أي طلب بعد.
													</p>
												</td>
											</tr>
										</tbody>
									</table>
									<hr class="mt-0 mb-3 pb-2" />

									<a href="category.html" class="btn btn-dark">اذهب للتسوق</a>
								</div>
							</div>
						</div><!-- End .tab-pane -->

						<div class="tab-pane fade" id="download" role="tabpanel">
							<div class="download-content">
								<h3 class="account-sub-title d-none d-md-block"><i
									class="sicon-cloud-download align-middle mr-3"></i>التنزيلات</h3>
								<div class="download-table-container">
								    <p>لا توجد تنزيلات متاحة بعد.</p> <a href="category.html"
									class="btn btn-primary text-transform-none mb-2">اذهب للتسوق</a>
								</div>
							</div>
						</div><!-- End .tab-pane -->

						<div class="tab-pane fade" id="address" role="tabpanel">
							<h3 class="account-sub-title d-none d-md-block mb-1"><i
								class="sicon-location-pin align-middle mr-3"></i>العناوين</h3>
							<div class="addresses-content">
								<p class="mb-4">
									سيتم استخدام العناوين التالية في صفحة الدفع بشكل افتراضي.
								</p>

								<div class="row">
									<div class="address col-md-6">
										<div class="heading d-flex">
											<h4 class="text-dark mb-0">عنوان الفواتير</h4>
										</div>

										<div class="address-box">
											لم تقم بإعداد هذا النوع من العناوين بعد.
										</div>

										<a href="#billing" class="btn btn-default address-action link-to-tab">إضافة عنوان</a>
									</div>

									<div class="address col-md-6 mt-5 mt-md-0">
										<div class="heading d-flex">
											<h4 class="text-dark mb-0">
												عنوان الشحن
											</h4>
										</div>

										<div class="address-box">
											لم تقم بإعداد هذا النوع من العناوين بعد.
										</div>

										<a href="#shipping" class="btn btn-default address-action link-to-tab">إضافة عنوان</a>
									</div>
								</div>
							</div>
						</div><!-- End .tab-pane -->

						<div class="tab-pane fade" id="edit" role="tabpanel">
							    <h3 class="account-sub-title d-none d-md-block mt-0 pt-1 ml-1"><i
								    class="icon-user-2 align-middle mr-3 pr-1"></i>تفاصيل الحساب</h3>
							<div class="account-content">
								<form action="#">
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="acc-name">الاسم الأول <span class="required">*</span></label>
												<input type="text" class="form-control" placeholder="الاسم الأول"
													id="acc-name" name="acc-name" required />
											</div>
										</div>

										<div class="col-md-6">
											<div class="form-group">
												<label for="acc-lastname">اسم العائلة <span
													class="required">*</span></label>
												<input type="text" class="form-control" id="acc-lastname"
												    name="acc-lastname" required />
											</div>
										</div>
									</div>

									<div class="form-group mb-2">
										<label for="acc-text">اسم العرض <span class="required">*</span></label>
										<input type="text" class="form-control" id="acc-text" name="acc-text"
											placeholder="اسم العرض" required />
										<p>سيتم عرض اسمك بهذا الشكل في قسم الحساب وفي التقييمات</p>
									</div>


									<div class="form-group mb-4">
										<label for="acc-email">البريد الإلكتروني <span class="required">*</span></label>
										<input type="email" class="form-control" id="acc-email" name="acc-email"
											placeholder="example@email.com" required />
									</div>

									<div class="change-password">
										<h3 class="text-uppercase mb-2">تغيير كلمة المرور</h3>

										<div class="form-group">
											<label for="acc-password">كلمة المرور الحالية (اتركها فارغة إذا كنت لا تريد التغيير)</label>
											<input type="password" class="form-control" id="acc-password"
												name="acc-password" />
										</div>

										<div class="form-group">
											<label for="acc-password">كلمة المرور الجديدة (اتركها فارغة إذا كنت لا تريد التغيير)</label>
											<input type="password" class="form-control" id="acc-new-password"
												name="acc-new-password" />
										</div>

										<div class="form-group">
											<label for="acc-password">تأكيد كلمة المرور الجديدة</label>
											<input type="password" class="form-control" id="acc-confirm-password"
												name="acc-confirm-password" />
										</div>
									</div>

									<div class="form-footer mt-3 mb-0">
										<button type="submit" class="btn btn-dark mr-0">
											حفظ التغييرات
										</button>
									</div>
								</form>
							</div>
						</div><!-- End .tab-pane -->

						<div class="tab-pane fade" id="billing" role="tabpanel">
							<div class="address account-content mt-0 pt-2">
								<h4 class="title">عنوان الفواتير</h4>

								<form class="mb-2" action="#">
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label>الاسم الأول <span class="required">*</span></label>
												<input type="text" class="form-control" required />
											</div>
										</div>

										<div class="col-md-6">
											<div class="form-group">
												<label>اسم العائلة <span class="required">*</span></label>
												<input type="text" class="form-control" required />
											</div>
										</div>
									</div>

									<div class="form-group">
										<label>الشركة </label>
										<input type="text" class="form-control">
									</div>

									<div class="select-custom">
										<label>الدولة / المنطقة <span class="required">*</span></label>
										<select name="orderby" class="form-control">
											<option value="" selected="selected">British Indian Ocean Territory
											</option>
											<option value="1">Brunei</option>
											<option value="2">Bulgaria</option>
											<option value="3">Burkina Faso</option>
											<option value="4">Burundi</option>
											<option value="5">Cameroon</option>
										</select>
									</div>

									<div class="form-group">
										<label>عنوان الشارع <span class="required">*</span></label>
										<input type="text" class="form-control"
											placeholder="رقم المنزل واسم الشارع" required />
										<input type="text" class="form-control"
											placeholder="شقة، جناح، وحدة، إلخ (اختياري)" required />
									</div>

									<div class="form-group">
										<label>المدينة <span class="required">*</span></label>
										<input type="text" class="form-control" required />
									</div>

									<div class="form-group">
										<label>المحافظة / الدولة <span class="required">*</span></label>
										<input type="text" class="form-control" required />
									</div>

									<div class="form-group">
										<label>الرمز البريدي <span class="required">*</span></label>
										<input type="text" class="form-control" required />
									</div>

									<div class="form-group mb-3">
										<label>رقم الهاتف <span class="required">*</span></label>
										<input type="number" class="form-control" required />
									</div>

									<div class="form-group mb-3">
										<label>البريد الإلكتروني <span class="required">*</span></label>
										<input type="email" class="form-control" placeholder="example@email.com"
											required />
									</div>

									<div class="form-footer mb-0">
										<div class="form-footer-right">
											<button type="submit" class="btn btn-dark py-4">
												حفظ العنوان
											</button>
										</div>
									</div>
								</form>
							</div>
						</div><!-- End .tab-pane -->

						<div class="tab-pane fade" id="shipping" role="tabpanel">
							<div class="address account-content mt-0 pt-2">
								<h4 class="title mb-3">عنوان الشحن</h4>

								<form class="mb-2" action="#">
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label>الاسم الأول <span class="required">*</span></label>
												<input type="text" class="form-control" required />
											</div>
										</div>

										<div class="col-md-6">
											<div class="form-group">
												<label>اسم العائلة <span class="required">*</span></label>
												<input type="text" class="form-control" required />
											</div>
										</div>
									</div>

									<div class="form-group">
										<label>الشركة </label>
										<input type="text" class="form-control">
									</div>

									<div class="select-custom">
										<label>الدولة / المنطقة <span class="required">*</span></label>
										<select name="orderby" class="form-control">
											<option value="" selected="selected">British Indian Ocean Territory
											</option>
											<option value="1">Brunei</option>
											<option value="2">Bulgaria</option>
											<option value="3">Burkina Faso</option>
											<option value="4">Burundi</option>
											<option value="5">Cameroon</option>
										</select>
									</div>

									<div class="form-group">
										<label>عنوان الشارع <span class="required">*</span></label>
										<input type="text" class="form-control"
											placeholder="رقم المنزل واسم الشارع" required />
										<input type="text" class="form-control"
											placeholder="شقة، جناح، وحدة، إلخ (اختياري)" required />
									</div>

									<div class="form-group">
										<label>المدينة <span class="required">*</span></label>
										<input type="text" class="form-control" required />
									</div>

									<div class="form-group">
										<label>المحافظة / الدولة <span class="required">*</span></label>
										<input type="text" class="form-control" required />
									</div>

									<div class="form-group">
										<label>الرمز البريدي <span class="required">*</span></label>
										<input type="text" class="form-control" required />
									</div>

									<div class="form-footer mb-0">
										<div class="form-footer-right">
											<button type="submit" class="btn btn-dark py-4">
												حفظ العنوان
											</button>
										</div>
									</div>
								</form>
							</div>
						</div><!-- End .tab-pane -->
					</div><!-- End .tab-content -->
				</div><!-- End .row -->
			</div>
    </FrontLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import FrontLayout from '@/Pages/Front/Theme1/Layout/App.vue';

function logout() {
	router.post(route('logout'));
}

// Props from the controller
const props = defineProps({
    category: Object,
    categories: Array,
    products: {
        type: Array,
        default: () => []
    },
    pagination: {
        type: Object,
        default: () => ({})
    }
});

// Reactive data
const selectedCategories = ref([]);
const minPrice = ref(null);
const maxPrice = ref(null);
const sortBy = ref('latest');
const perPage = ref(12);

// Methods
const filterProducts = () => {
    // Implement product filtering logic here
    console.log('Filtering products...');
};

const addToCart = (product) => {
    // Implement add to cart functionality
    console.log('Adding to cart:', product);
};

const getProductImage = (product) => {
    if (product.images && product.images.length > 0) {
        let img = product.images[0];
        // إذا كان المسار لا يبدأ بـ http أو /
        if (!img.startsWith('http') && !img.startsWith('/')) {
            img = '/' + img;
        }
        return img;
    }
    return '/front/theme1/images/demoes/demo3/products/product-1.jpg';
};

onMounted(() => {
    // Initialize component
    console.log('Shop component mounted');
    if (props.category) {
        console.log('Current category:', props.category);
    }
});
</script>


