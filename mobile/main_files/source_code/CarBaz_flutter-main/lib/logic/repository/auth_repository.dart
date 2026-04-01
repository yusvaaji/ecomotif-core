import 'package:dartz/dartz.dart';
import '../../data/data_provider/local_data_source.dart';
import '../../data/data_provider/remote_data_source.dart';
import '../../data/model/auth/login_state_model.dart';
import '../../data/model/auth/register_state_model.dart';
import '../../data/model/auth/user_response_model.dart';
import '../../presentation/errors/exception.dart';
import '../../presentation/errors/failure.dart';
import '../cubit/forgot_password/forgot_password_state_model.dart';

abstract class AuthRepository {
  Future<Either<dynamic, UserResponseModel>> login(LoginStateModel body);

  Future<Either<Failure, String>> logout(Uri uri);

  Either<Failure, UserResponseModel> getExistingUserInfo();

  ///registration related class
  Future<Either<dynamic, String>> signUp(RegisterStateModel body);

 Future<Either<dynamic, String>> verifyRegOtp(RegisterStateModel body);
//
// Future<Either<Failure, GetLocationDataModel>> getLocationData(SignUpStateModel body);
//
//
 Future<Either<dynamic, String>> forgotPassword(PasswordStateModel body);

 Future<Either<dynamic, String>> forgotOtpVerify(PasswordStateModel body);

 Future<Either<dynamic, String>> setResetPassword(PasswordStateModel body);

 Future<Either<dynamic, String>> updatePassword(PasswordStateModel body, Uri url);
}

class AuthRepositoryImpl implements AuthRepository {
  final RemoteDataSources remoteDataSources;
  final LocalDataSources localDataSources;

  AuthRepositoryImpl(
      {required this.remoteDataSources, required this.localDataSources});

  @override
  Future<Either<dynamic, UserResponseModel>> login(LoginStateModel body) async {
    try {
      final result = await remoteDataSources.login(body);
      final response = UserResponseModel.fromMap(result);
      localDataSources.cacheUserResponse(response);
      return Right(response);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    } on InvalidAuthData catch (e) {
      return Left(InvalidAuthData(e.errors));
    }
  }

  @override
  Either<Failure, UserResponseModel> getExistingUserInfo() {
    try {
      final result = localDataSources.getExistingUserInfo();
      return Right(result);
    } on DatabaseException catch (e) {
      return Left(DatabaseFailure(e.message));
    }
  }

  @override
  Future<Either<Failure, String>> logout(Uri uri) async {
    try {
      final logout = await remoteDataSources.logout(uri);
      localDataSources.clearUserResponse();
      return Right(logout['message']);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    }
  }


  @override
  Future<Either<dynamic, String>> signUp(RegisterStateModel body) async {
    try {
      final result = await remoteDataSources.register(body);
      return Right(result['message']);
    } on InvalidAuthData catch (e) {
      return Left(InvalidAuthData(e.errors));
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    }
  }

@override
Future<Either<dynamic, String>> verifyRegOtp(RegisterStateModel body) async {
  try {
    final result = await remoteDataSources.otpVerify(body);
    return Right(result['message']);
  } on ServerException catch (e) {
    return Left(ServerFailure(e.message, e.statusCode));
  } on InvalidAuthData catch (e) {
    return Left(InvalidAuthData(e.errors));
  }
}


// @override
// Future<Either<Failure, GetLocationDataModel>> getLocationData(SignUpStateModel body) async {
//   try {
//     final result = await remoteDataSources.getLocationData(body);
//     final data = GetLocationDataModel.fromMap(result);
//     return Right(data);
//   } on ServerException catch (e) {
//     return Left(ServerFailure(e.message, e.statusCode));
//   }
// }
//
@override
Future<Either<dynamic, String>> forgotPassword(PasswordStateModel body) async {
  try {
    final result = await remoteDataSources.forgotPassword(body);
    return Right(result['message']);
  } on ServerException catch (e) {
    return Left(ServerFailure(e.message, e.statusCode));
  } on InvalidAuthData catch (e) {
    return Left(InvalidAuthData(e.errors));
  }
}

@override
Future<Either<dynamic, String>> forgotOtpVerify(PasswordStateModel body) async {
  try {
    final result = await remoteDataSources.forgotOtpVerify(body);
    return Right(result['message']);
  } on ServerException catch (e) {
    return Left(ServerFailure(e.message, e.statusCode));
  } on InvalidAuthData catch (e) {
    return Left(InvalidAuthData(e.errors));
  }
}

@override
Future<Either<dynamic, String>> setResetPassword(PasswordStateModel body) async {
  try {
    final result = await remoteDataSources.setResetPassword(body);
    return Right(result['message']);
  } on ServerException catch (e) {
    return Left(ServerFailure(e.message, e.statusCode));
  } on InvalidAuthData catch (e) {
    return Left(InvalidAuthData(e.errors));
  }
}

  @override
  Future<Either<dynamic, String>> updatePassword(PasswordStateModel body, Uri url) async {
    try {
      final result = await remoteDataSources.updatePassword(body, url);
      return Right(result['message']);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    } on InvalidAuthData catch (e) {
      return Left(InvalidAuthData(e.errors));
    }
  }
}
