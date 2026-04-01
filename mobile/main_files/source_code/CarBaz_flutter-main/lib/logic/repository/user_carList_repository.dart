import 'package:ecomotif/data/model/car/car_state_model.dart';
import 'package:ecomotif/data/model/home/home_model.dart';
import 'package:dartz/dartz.dart';
import '../../data/data_provider/remote_data_source.dart';
import '../../data/model/car/car_create_data_model.dart';
import '../../data/model/car/create_model_response.dart';
import '../../data/model/car/getCarEditDataModel.dart';
import '../../presentation/errors/exception.dart';
import '../../presentation/errors/failure.dart';

abstract class UserCarListRepository {
  Future<Either<Failure, List<FeaturedCars>>> getCarList(Uri url);

  Future<Either<Failure, CarCreateDataModel>> getCarCreateData(Uri url);

  Future<Either<Failure, CarEditDataModel>> getCarEditData(Uri url);

  Future<Either<dynamic, CreateModelResponse>> addCars(
    CarsStateModel body,
    Uri url,
  );

  Future<Either<dynamic, CreateModelResponse>> updateBasicCar(
      CarsStateModel body,
      Uri url,
      );

  Future<Either<dynamic, CreateModelResponse>> keyFeatureUpdateCars(
      CarsStateModel body,
      Uri url,
      );

  Future<Either<dynamic, CreateModelResponse>> featureUpdateCars(
      CarsStateModel body,
      Uri url,
      );

  Future<Either<dynamic, CreateModelResponse>> addressUpdateCars(
      CarsStateModel body,
      Uri url,
      );

  Future<Either<dynamic, CreateModelResponse>> galleryUpdateCars(
      CarsStateModel body,
      Uri url,
      );

  Future<Either<Failure, String>> deleteCar(Uri url);
}

class UserCarListRepositoryImpl implements UserCarListRepository {
  final RemoteDataSources remoteDataSource;

  const UserCarListRepositoryImpl({required this.remoteDataSource});

  @override
  Future<Either<Failure, List<FeaturedCars>>> getCarList(Uri url) async {
    try {
      final result = await remoteDataSource.getUserWithdraw(url);
      final wish = result['cars']['data'] as List;
      final data =
          List<FeaturedCars>.from(wish.map((e) => FeaturedCars.fromMap(e)))
              .toList();
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    }
  }

  @override
  Future<Either<Failure, CarCreateDataModel>> getCarCreateData(Uri url) async {
    try {
      final result = await remoteDataSource.getCarCreateData(url);
      final data = CarCreateDataModel.fromMap(result);
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    }
  }


  @override
  Future<Either<Failure, CarEditDataModel>> getCarEditData(Uri url) async {
    try {
      final result = await remoteDataSource.getCarEditData(url);
      final data = CarEditDataModel.fromMap(result);
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    }
  }

  @override
  Future<Either<Failure, CreateModelResponse>> addCars(
    CarsStateModel body,
    Uri url,
  ) async {
    try {
      final result = await remoteDataSource.addCar(body, url);
      final data = CreateModelResponse.fromMap(result);
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    } on InvalidAuthData catch (e) {
      return Left(InvalidAuthData(e.errors));
    }
  }


  @override
  Future<Either<Failure, CreateModelResponse>> updateBasicCar(CarsStateModel body, Uri url,) async {
    try {
      final result = await remoteDataSource.updateBasicCar(body, url);
      final data = CreateModelResponse.fromMap(result);
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    } on InvalidAuthData catch (e) {
      return Left(InvalidAuthData(e.errors));
    }
  }

  @override
  Future<Either<Failure, CreateModelResponse>> keyFeatureUpdateCars(CarsStateModel body, Uri url,) async {
    try {
      final result = await remoteDataSource.keyFeatureUpdateCar(body, url);
      final data = CreateModelResponse.fromMap(result);
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    } on InvalidAuthData catch (e) {
      return Left(InvalidAuthData(e.errors));
    }
  }

  @override
  Future<Either<Failure, CreateModelResponse>> featureUpdateCars(CarsStateModel body, Uri url,) async {
    try {
      final result = await remoteDataSource.featureUpdateCar(body, url);
      final data = CreateModelResponse.fromMap(result);
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    } on InvalidAuthData catch (e) {
      return Left(InvalidAuthData(e.errors));
    }
  }

  @override
  Future<Either<Failure, CreateModelResponse>> addressUpdateCars(CarsStateModel body, Uri url,) async {
    try {
      final result = await remoteDataSource.addressUpdateCar(body, url);
      final data = CreateModelResponse.fromMap(result);
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    } on InvalidAuthData catch (e) {
      return Left(InvalidAuthData(e.errors));
    }
  }

  @override
  Future<Either<Failure, CreateModelResponse>> galleryUpdateCars(CarsStateModel body, Uri url,) async {
    try {
      final result = await remoteDataSource.galleryUpdateCar(body, url);
      final data = CreateModelResponse.fromMap(result);
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    } on InvalidAuthData catch (e) {
      return Left(InvalidAuthData(e.errors));
    }
  }


  @override
  Future<Either<Failure, String>> deleteCar(Uri url) async {
    try {
      final result = await remoteDataSource.deleteCar(url);
      return Right(result);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    }
  }
}
