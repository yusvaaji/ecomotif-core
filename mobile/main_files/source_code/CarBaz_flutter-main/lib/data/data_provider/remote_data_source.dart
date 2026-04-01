import 'dart:developer';
import 'dart:io';
import 'package:ecomotif/data/data_provider/remote_url.dart';
import 'package:ecomotif/data/model/auth/register_state_model.dart';
import 'package:ecomotif/data/model/auth/user_response_model.dart';
import 'package:ecomotif/data/model/car/car_state_model.dart';
import 'package:http/http.dart' as http;
import '../../logic/cubit/forgot_password/forgot_password_state_model.dart';
import '../model/auth/login_state_model.dart';
import '../model/kyc/kyc_submit_state_model.dart';
import 'network_parser.dart';

abstract class RemoteDataSources {
  Future getWebsiteSetup(Uri uri);

  Future getHomeData(String langCode);

  Future getAllCarsList(Uri url);

  Future getAllDealerList(Uri url);

  Future getCarsDetails(String langCode, String id);

  Future contactDealer(String langCode, String id, Map<String, dynamic> body);

  Future contactMessage(String langCode, Map<String, dynamic> body);

  Future getDealerDetails(String langCode, String userName);

  Future login(LoginStateModel body);

  Future register(RegisterStateModel body);

  Future otpVerify(RegisterStateModel body);

  Future forgotOtpVerify(PasswordStateModel body);

  Future forgotPassword(PasswordStateModel body);

  Future setResetPassword(PasswordStateModel body);

  Future updatePassword(PasswordStateModel body, Uri url);

  Future logout(Uri uri);

  Future getWishList(Uri url);

  Future addWishList(Uri url);

  Future removeWishList(Uri url);

  Future removeCompareList(Uri url);

  Future getBookingHistoryList(Uri url);

  Future getBookingHistoryDetails(Uri url, String id);

  Future getBookingHistoryCancel(Uri url, String id);

  Future startRide(Uri url, String id);

  Future getTransactionsList(Uri url);

  Future getUserDashboard(Uri url);

  Future getUserWithdraw(Uri url);

  Future getBookingRequest(Uri url);

  Future getUserCarList(Uri url);

  Future getCarCreateData(Uri url);

  Future getCarEditData(Uri url);

  Future addCar(CarsStateModel body, Uri url);

  Future updateBasicCar(CarsStateModel body, Uri url);

  Future keyFeatureUpdateCar(CarsStateModel body, Uri url);
  Future featureUpdateCar(CarsStateModel body, Uri url);
  Future addressUpdateCar(CarsStateModel body, Uri url);
  Future galleryUpdateCar(CarsStateModel body, Uri url);

  Future deleteCar(Uri url);

  Future getKycInfo(Uri url);

  Future<String> submitKycVerify(Uri url, KycSubmitStateModel data);

  Future getAllReview(Uri url);

  Future getTermsCondition(String langCode);
  Future getPrivacyPolicy(String langCode);

  Future getProfileData(Uri url);

  Future updateProfile(User body, Uri url);

  Future getSearchAttribute(Uri url);

  Future getCity(Uri url, String id);

  Future getDealerCity(Uri url);

  Future getCompareList(Uri url);

  Future subscriptionPlanList(Uri url);

  Future paymentInfo(Uri url);

  Future freePlanEnroll(String id, Uri url);

  Future storeReview(Uri url, Map<String, dynamic> body);

  Future addCompareList(Uri url, Map<String, dynamic> body);

  Future payWithStripe(Uri url, Map<String, dynamic> body);

  Future payWithBank(Uri url, Map<String, dynamic> body);


  Future transactionList(Uri url);


}

typedef CallClientMethod = Future<http.Response> Function();

class RemoteDataSourcesImpl extends RemoteDataSources {
  final http.Client client;

  RemoteDataSourcesImpl({required this.client});

  final headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  };

  final postDeleteHeader = {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  };

  @override
  Future login(LoginStateModel body) async {
    final uri = Uri.parse(RemoteUrls.login)
        .replace(queryParameters: {'lang_code': body.languageCode});
    print("login url : $uri");
    final clientMethod =
        client.post(uri, body: body.toMap(), headers: postDeleteHeader);
    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future register(RegisterStateModel body) async {
    final uri = Uri.parse(RemoteUrls.register)
        .replace(queryParameters: {'lang_code': body.languageCode});
    print("login url : $uri");
    final clientMethod =
    client.post(uri, body: body.toMap(), headers: postDeleteHeader);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future otpVerify(RegisterStateModel body) async {
    final uri = Uri.parse(RemoteUrls.otpVerify)
        .replace(queryParameters: {'lang_code': body.languageCode});
    print("login url : $uri");
    final clientMethod =
    client.post(uri, body: body.toMap(), headers: postDeleteHeader);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future forgotOtpVerify(PasswordStateModel body) async {
    final uri = Uri.parse(RemoteUrls.forgotOtpVerify)
        .replace(queryParameters: {'lang_code': body.languageCode});
    print("login url : $uri");
    final clientMethod =
    client.post(uri, body: body.toMap(), headers: postDeleteHeader);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future forgotPassword(PasswordStateModel body) async {
    final uri = Uri.parse(RemoteUrls.forgotPassword)
        .replace(queryParameters: {'lang_code': body.languageCode});
    print("login url : $uri");
    final clientMethod =
    client.post(uri, body: body.toMap(), headers: postDeleteHeader);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }


  @override
  Future setResetPassword(PasswordStateModel body) async {
    final uri = Uri.parse(RemoteUrls.setResetPassword)
        .replace(queryParameters: {'lang_code': body.languageCode});
    print("login url : $uri");
    final clientMethod =
    client.post(uri, body: body.toMap(), headers: postDeleteHeader);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future updatePassword(PasswordStateModel body, Uri url) async {
    final clientMethod =
    client.post(url, body: body.toMap(), headers: postDeleteHeader);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future logout(Uri uri) async {
    final clientMethod = client.get(uri, headers: headers);
    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getWebsiteSetup(Uri uri) async {
    final clientMethod = client.get(uri, headers: headers);
    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getHomeData(String langCode) async {
    final uri = Uri.parse(RemoteUrls.homeUrl).replace(queryParameters: {
      'lang_code': langCode,
    });
    final clientMethod = client.get(uri, headers: headers);
    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getAllCarsList(Uri url) async {
    // final uri = Uri.parse(RemoteUrls.allCarList).replace(queryParameters: {
    //   'lang_code': langCode,
    // });
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getAllDealerList(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getCarsDetails(String langCode, String id) async {
    final uri = Uri.parse(RemoteUrls.carDetails(id)).replace(queryParameters: {
      'lang_code': langCode,
    });
    final clientMethod = client.get(uri, headers: headers);
    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getDealerDetails(String langCode, String userName) async {
    final uri = Uri.parse(RemoteUrls.dealerDetails(userName)).replace(queryParameters: {
      'lang_code': langCode,
    });
    final clientMethod = client.get(uri, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }


  @override
  Future contactDealer(String langCode, String id, Map<String, dynamic> body) async {
    final uri = Uri.parse(RemoteUrls.contactDealer(id)).replace(queryParameters: {
      'lang_code': langCode,
    });

    final clientMethod =
    client.post(uri, headers: postDeleteHeader, body: body);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody['message'] as String;
  }


  @override
  Future contactMessage(String langCode,  Map<String, dynamic> body) async {
    final uri = Uri.parse(RemoteUrls.contactMessage).replace(queryParameters: {
      'lang_code': langCode,
    });

    final clientMethod =
    client.post(uri, headers: postDeleteHeader, body: body);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody['message'] as String;
  }

  @override
  Future getWishList(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future addWishList(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody['message'] as String;
  }

  @override
  Future removeWishList(Uri url) async {
    final clientMethod = client.delete(url, headers: headers);
    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody['message'] as String;
  }

  @override
  Future removeCompareList(Uri url) async {
    final clientMethod = client.delete(url, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody['message'] as String;
  }

  @override
  Future getBookingHistoryList(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getBookingHistoryDetails(Uri url, String id) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getBookingHistoryCancel(Uri url, String id) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody['message'] as String;
  }

  @override
  Future startRide(Uri url, String id) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody['message'] as String;
  }

  @override
  Future getTransactionsList(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getUserDashboard(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getUserWithdraw(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getBookingRequest(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getUserCarList(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getCarCreateData(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getCarEditData(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }


  @override
  Future addCar(
    CarsStateModel body,
    Uri url,
  ) async {
    final request = http.MultipartRequest('POST', url);
    request.fields.addAll(body.toMap());

    request.headers.addAll(postDeleteHeader);
    if (body.thumbImage.isNotEmpty) {
      final file =
          await http.MultipartFile.fromPath('thumb_image', body.thumbImage);
      request.files.add(file);
    }

    http.StreamedResponse response = await request.send();
    final clientMethod = http.Response.fromStream(response);

    final responseJsonBody =
        await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }


  @override
  Future updateBasicCar(
      CarsStateModel body,
      Uri url,
      ) async {
    final request = http.MultipartRequest('POST', url);
    request.fields.addAll(body.toMap());

    request.headers.addAll(postDeleteHeader);

    http.StreamedResponse response = await request.send();
    final clientMethod = http.Response.fromStream(response);

    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future keyFeatureUpdateCar(
      CarsStateModel body,
      Uri url,
      ) async {
    final request = http.MultipartRequest('POST', url);
    request.fields.addAll(body.toMap());

    request.headers.addAll(postDeleteHeader);

    http.StreamedResponse response = await request.send();
    final clientMethod = http.Response.fromStream(response);

    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future featureUpdateCar(
      CarsStateModel body,
      Uri url,
      ) async {
    final request = http.MultipartRequest('POST', url);
    request.fields.addAll(body.toMap());

    request.headers.addAll(postDeleteHeader);

    http.StreamedResponse response = await request.send();
    final clientMethod = http.Response.fromStream(response);

    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future addressUpdateCar(
      CarsStateModel body,
      Uri url,
      ) async {
    final request = http.MultipartRequest('POST', url);
    request.fields.addAll(body.toMap());

    request.headers.addAll(postDeleteHeader);

    http.StreamedResponse response = await request.send();
    final clientMethod = http.Response.fromStream(response);

    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future galleryUpdateCar(
      CarsStateModel body,
      Uri url,
      ) async {
    final request = http.MultipartRequest('POST', url);
    request.fields.addAll(body.toMap());

    request.headers.addAll(postDeleteHeader);
    if (body.thumbImage.isNotEmpty) {
      final file =
      await http.MultipartFile.fromPath('thumb_image', body.thumbImage);
      request.files.add(file);
    }
    if (body.videoImage.isNotEmpty) {
      final file =
      await http.MultipartFile.fromPath('video_image', body.videoImage);
      request.files.add(file);
    }

    if (body.galleryImages!.isNotEmpty) {
      for (int i = 0; i < body.galleryImages!.length; i++) {
        if (body.galleryImages![i].isNotEmpty &&
            !body.galleryImages![i].contains('https://')) {
          final galleries = await http.MultipartFile.fromPath(
              'file[$i]', body.galleryImages![i]);
          request.files.add(galleries);
        }
      }
    }

    http.StreamedResponse response = await request.send();
    final clientMethod = http.Response.fromStream(response);

    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }


  @override
  Future deleteCar(Uri url) async {
    final clientMethod = client.delete(url, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody['message'] as String;
  }

  @override
  Future getKycInfo(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future<String> submitKycVerify(url, KycSubmitStateModel data) async {
    final headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest',
    };

    final request = http.MultipartRequest('POST', url);
    request.fields.addAll(data.toMap());
    request.headers.addAll(headers);
    if (data.file.isNotEmpty) {
      final file = await http.MultipartFile.fromPath('file', data.file);
      request.files.add(file);
    }

    http.StreamedResponse response = await request.send();
    final clientMethod = http.Response.fromStream(response);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody['message'] as String;
  }

  @override
  Future getAllReview(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getTermsCondition(String langCode) async {
    final uri = Uri.parse(RemoteUrls.getTermsCondition).replace(queryParameters: {
      'lang_code': langCode,
    });
    final clientMethod = client.get(uri, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getPrivacyPolicy(String langCode) async {
    final uri = Uri.parse(RemoteUrls.getPrivacyPolicy).replace(queryParameters: {
      'lang_code': langCode,
    });
    final clientMethod = client.get(uri, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getProfileData(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }


  @override
  Future<String> updateProfile(User body, Uri url) async {

    final request = http.MultipartRequest('POST', url);
    request.fields.addAll(body.toMap());

    if (body.image.isNotEmpty) {
      final file = await http.MultipartFile.fromPath('image', body.image);
      request.files.add(file);
    }
    http.StreamedResponse response = await request.send();
    final clientMethod = http.Response.fromStream(response);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody['message'] as String;
  }

  @override
  Future getSearchAttribute(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getCity(Uri url, String id) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getDealerCity(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future getCompareList(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future subscriptionPlanList(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future paymentInfo(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }

  @override
  Future freePlanEnroll(String id, Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody['message'] as String;
  }


  @override
  Future storeReview(Uri url, Map<String, dynamic> body) async {
    final clientMethod =
    client.post(url, headers: postDeleteHeader, body: body);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody['message'] as String;
  }

  @override
  Future addCompareList(Uri url, Map<String, dynamic> body) async {
    final clientMethod =
    client.post(url, headers: postDeleteHeader, body: body);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody['message'] as String;
  }

  @override
  Future payWithStripe(Uri url, Map<String, dynamic> body) async {
    final clientMethod =
    client.post(url, headers: postDeleteHeader, body: body);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody['message'] as String;
  }

  @override
  Future payWithBank(Uri url, Map<String, dynamic> body) async {
    final clientMethod =
    client.post(url, headers: postDeleteHeader, body: body);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody['message'] as String;
  }

  @override
  Future transactionList(Uri url) async {
    final clientMethod = client.get(url, headers: headers);
    final responseJsonBody =
    await NetworkParser.callClientWithCatchException(() => clientMethod);
    return responseJsonBody;
  }
}
