class RemoteUrls {
  // Untuk emulator Android, gunakan 10.0.2.2 agar mengarah ke host (Laravel lokal).
  // Sesuaikan port jika server Laravel berbeda dari 8000.
  static const String rootUrl = "http://10.0.2.2:8000/";
  // static const String rootUrl = "http://192.168.68.110/ecomotif/"; // contoh jaringan LAN
  // static const String rootUrl = "https://carbazapi-laravel.mamunuiux.com/";
  // static const String rootUrl = "https://carbaz.mamunuiux.com/";
  static const String baseUrl = '${rootUrl}api/';
  static const String websiteSetup = '${baseUrl}website-setup';
  static const String homeUrl = baseUrl;
  static const String login = '${baseUrl}store-login';
  static const String register = '${baseUrl}store-register';
  static const String otpVerify = '${baseUrl}user-verification';
  static const String forgotOtpVerify = '${baseUrl}verify-forget-password-otp';
  static const String forgotPassword = '${baseUrl}send-forget-password';
  static const String setResetPassword = '${baseUrl}store-reset-password';
  static const String updatePassword = '${baseUrl}user/update-password';
  static const String logout = '${baseUrl}user-logout';
  static const String allCarList = '${baseUrl}listings';
  static  String carDetails(String id) => '${baseUrl}listing/$id';
  static const String wishLists = '${baseUrl}user/wishlists';
  static  String addWishLists(String id) => '${baseUrl}user/add-to-wishlist/$id';
  static  String removeWishLists(String id) => '${baseUrl}user/remove-wishlist/$id';
  static const String bookingHistory = '${baseUrl}user/booking-history';
  static  String bookingHistoryDetails(String id) => '${baseUrl}user/booking-details/$id';
  static  String bookingHistoryCancel(String id) => '${baseUrl}user/booking-cancel-by-user/$id';
  static  String startRide(String id) => '${baseUrl}user/ride-start-by-user/$id';
  static  String getTransactions = '${baseUrl}user/transactions';
  static  String getUserDashboard = '${baseUrl}user/dashboard';
  static  String getUserWithdraw = '${baseUrl}user/my-withdraw';
  static  String getUserBookingRequest = '${baseUrl}user/booking-request';
  static  String getUserCarList = '${baseUrl}user/car' ;
  static  String createCar = '${baseUrl}user/car' ;
  static  String getEditCarData(String id) => '${baseUrl}user/car/$id/edit' ;
  static  String updateBasicCar(String id) => '${baseUrl}user/car/$id' ;
  static  String keyFeatureUpdateCar(String id) => '${baseUrl}user/car-key-feature/$id';
  static  String featureUpdateCar(String id) => '${baseUrl}user/car-feature/$id';
  static  String addressUpdateCar(String id) => '${baseUrl}user/car-address/$id';
  static  String galleryUpdateCar(String id) => '${baseUrl}user/video-images/$id';
  static  String deleteCar(String id) => '${baseUrl}user/car/$id' ;
  static  String getCarCreateData = '${baseUrl}user/car/create' ;
  static  String getKycInfo = '${baseUrl}user/kyc' ;
  static  String submitKycInfo = '${baseUrl}user/kyc-submit' ;
  static  String getAllReview = '${baseUrl}user/reviews' ;
  static  String getTermsCondition = '${baseUrl}terms-conditions' ;
  static  String getPrivacyPolicy = '${baseUrl}privacy-policy' ;
  static  String getProfileData = '${baseUrl}user/edit-profile?' ;
  static  String updateProfile = '${baseUrl}user/update-profile' ;
  static  String getSearchAttribute = '${baseUrl}listings-filter-option' ;
  static  String getCity(String id) => '${baseUrl}cities-by-country/$id' ;
  static  String dealerDetails(userName) => '${baseUrl}dealer/$userName' ;
  static  String contactDealer(carId) => '${baseUrl}send-message-to-dealer/$carId' ;
  static  String contactMessage = '${baseUrl}store-contact-message';
  static  String allDealer = '${baseUrl}dealers';
  static  String getDealerCity = '${baseUrl}dealers-filter-option';
  static  String storeReview = '${baseUrl}user/store-review';
  static  String compareList = '${baseUrl}user/comparelist';
  static  String addCompareList = '${baseUrl}user/comparelist';
  static  String removeCompareList(String id) => '${baseUrl}user/comparelist/$id';


  static  String subscriptionPlanList = '${baseUrl}user/pricing-plan';
  static  String freePlanEnroll(String id) => '${baseUrl}user/free-enroll/$id';
  static  String paymentInfo = '${baseUrl}user/payment-info';
  static  String payWithStripe(String id) => '${baseUrl}user/pay-with-stripe/$id';
  static  String payWithBank(String id) => '${baseUrl}user/pay-with-bank/$id';
  static  String payWithRazorpay(String id) => '${rootUrl}user/razorpay-webview/$id';
  static  String payWithFlutterWave(String id) => '${rootUrl}user/flutterwave-webview/$id';
  static  String payWithPayStack(String id) => '${rootUrl}user/paystack-webview/$id';
  static  String payWithMollie(String id) => '${rootUrl}user/mollie-webview/$id';
  static  String payWithInstamojo(String id) => '${rootUrl}user/instamojo-webview/$id';
  static  String payWithPaypal(String id) => '${rootUrl}pay-with-paypal-webview/$id';

  static  String transactionList = '${baseUrl}user/transactions';





  static imageUrl(String imageUrl) => rootUrl + imageUrl;
}