import 'package:ecomotif/data/data_provider/remote_data_source.dart';
import 'package:ecomotif/data/model/cars_details/car_details_model.dart';
import 'package:ecomotif/data/model/home/dealer_details_model.dart';
import 'package:ecomotif/data/model/search_attribute/search_attribute_model.dart';
import 'package:dartz/dartz.dart';
import '../../data/model/home/home_model.dart';
import '../../presentation/errors/exception.dart';
import '../../presentation/errors/failure.dart';

abstract class HomeRepository {
  Future<Either<Failure, HomeModel>> getHomeData(String langCode);

  Future<Either<Failure, CarDetailsModel>> carDetails(
      String langCode, String id);

  Future<Either<Failure, DealerDetailsModel>> dealerDetails(
      String langCode, String userName);

  Future<Either<Failure, List<FeaturedCars>>> getAllCarList(Uri url);

  Future<Either<Failure, List<Dealers>>> getAllDealerList(Uri url);

  Future<Either<Failure, SearchAttributeModel>> getSearchAttribute(Uri url);

  Future<Either<Failure, CityModel>> getCity(Uri url, String id);

  Future<Either<Failure, CityModel>> getDealerCity(Uri url);

  Future<Either<Failure, String>> contactDealer(
      String langCode, String id, Map<String, dynamic> body);

  Future<Either<Failure, String>> contactMessage(
      String langCode,  Map<String, dynamic> body);
}

class HomeRepositoryImpl implements HomeRepository {
  final RemoteDataSources remoteDataSource;

  const HomeRepositoryImpl({required this.remoteDataSource});

  @override
  Future<Either<Failure, HomeModel>> getHomeData(String langCode) async {
    try {
      final result = await remoteDataSource.getHomeData(langCode);
      final data = HomeModel.fromMap(result);
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    }
  }

  @override
  Future<Either<Failure, List<FeaturedCars>>> getAllCarList(
      Uri url) async {
    try {
      final result = await remoteDataSource.getAllCarsList(url);
      final cars = result['cars']['data'] as List;
      final data =
          List<FeaturedCars>.from(cars.map((e) => FeaturedCars.fromMap(e)))
              .toList();
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    }
  }


  @override
  Future<Either<Failure, List<Dealers>>> getAllDealerList(
      Uri url) async {
    try {
      final result = await remoteDataSource.getAllDealerList(url);
      final dealer = result['dealers']['data'] as List;
      final data =
      List<Dealers>.from(dealer.map((e) => Dealers.fromMap(e)))
          .toList();
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    }
  }

  @override
  Future<Either<Failure, CarDetailsModel>> carDetails(
      String langCode, String id) async {
    try {
      final result = await remoteDataSource.getCarsDetails(langCode, id);
      final data = CarDetailsModel.fromMap(result);
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    }
  }

  @override
  Future<Either<Failure, DealerDetailsModel>> dealerDetails(
      String langCode, String userName) async {
    try {
      final result = await remoteDataSource.getDealerDetails(langCode, userName);
      final data = DealerDetailsModel.fromMap(result);
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    }
  }

  @override
  Future<Either<Failure, SearchAttributeModel>> getSearchAttribute(
      Uri url) async {
    try {
      final result = await remoteDataSource.getSearchAttribute(url);
      final data = SearchAttributeModel.fromMap(result);
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    }
  }

  @override
  Future<Either<Failure, CityModel>> getCity(
      Uri url, String id) async {
    try {
      final result = await remoteDataSource.getCity(url, id);
      final data = CityModel.fromMap(result);
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    }
  }

  @override
  Future<Either<Failure, CityModel>> getDealerCity(
      Uri url) async {
    try {
      final result = await remoteDataSource.getDealerCity(url);
      final data = CityModel.fromMap(result);
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    }
  }


  @override
  Future<Either<Failure, String>> contactDealer(
      String langCode, String id, Map<String, dynamic> body) async {
    try {
      final result = await remoteDataSource.contactDealer(langCode, id,body);
      return Right(result);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    } on InvalidAuthData catch (e) {
      return Left(InvalidAuthData(e.errors));
    }
  }


  @override
  Future<Either<Failure, String>> contactMessage(
      String langCode, Map<String, dynamic> body) async {
    try {
      final result = await remoteDataSource.contactMessage(langCode,body);
      return Right(result);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    } on InvalidAuthData catch (e) {
      return Left(InvalidAuthData(e.errors));
    }
  }
}
