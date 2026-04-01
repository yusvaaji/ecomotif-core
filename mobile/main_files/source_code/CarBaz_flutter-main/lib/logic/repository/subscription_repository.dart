import 'package:ecomotif/data/model/payment_model/payment_info_model.dart';
import 'package:ecomotif/data/model/subscription/subscription_list_model.dart';
import 'package:ecomotif/data/model/subscription/transaction_model.dart';
import 'package:dartz/dartz.dart';
import '../../data/data_provider/remote_data_source.dart';
import '../../presentation/errors/exception.dart';
import '../../presentation/errors/failure.dart';

abstract class SubscriptionRepository {

  Future<Either<Failure, List<SubscriptionListModel>>> subscriptionPlanList(Uri url);

  Future<Either<Failure, PaymentInfoModel>> paymentInfo(Uri url);

  Future<Either<Failure, String>> payWithStripe(
      Uri url,  Map<String, dynamic> body);

  Future<Either<Failure, String>> payWithBank(
      Uri url,  Map<String, dynamic> body);

  Future<Either<Failure, String>> freePlanEnroll(String id, Uri url);

  Future<Either<Failure, List<TransactionModel>>> transactionList( Uri url);

}

class SubscriptionRepositoryImpl implements SubscriptionRepository {
  final RemoteDataSources remoteDataSource;

  const SubscriptionRepositoryImpl({required this.remoteDataSource});


  @override
  Future<Either<Failure, List<SubscriptionListModel>>> subscriptionPlanList(Uri url) async {
    try {
      final result = await remoteDataSource.subscriptionPlanList(url);
      final plan = result['plans'] as List;
      final data =
      List<SubscriptionListModel>.from(plan.map((e) => SubscriptionListModel.fromMap(e)))
          .toList();
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    }
  }

  @override
  Future<Either<Failure, PaymentInfoModel>> paymentInfo(Uri url) async {
    try {
      final result = await remoteDataSource.paymentInfo(url);
      final data = PaymentInfoModel.fromMap(result);
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    }
  }


  @override
  Future<Either<Failure, String>> payWithStripe(
      Uri url, Map<String, dynamic> body) async {
    try {
      final result = await remoteDataSource.payWithStripe(url,body);
      return Right(result);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    } on InvalidAuthData catch (e) {
      return Left(InvalidAuthData(e.errors));
    }
  }

  @override
  Future<Either<Failure, String>> payWithBank(
      Uri url, Map<String, dynamic> body) async {
    try {
      final result = await remoteDataSource.payWithBank(url,body);
      return Right(result);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    } on InvalidAuthData catch (e) {
      return Left(InvalidAuthData(e.errors));
    }
  }

  @override
  Future<Either<Failure, String>> freePlanEnroll(String id, Uri url) async {
    try {
      final result = await remoteDataSource.freePlanEnroll(id, url);
      return Right(result);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    }
  }

  @override
  Future<Either<Failure, List<TransactionModel>>>  transactionList(Uri url) async {
    try {
      final result = await remoteDataSource.transactionList(url);
      final plan = result['histories'] as List;
      final data =
      List<TransactionModel>.from(plan.map((e) => TransactionModel.fromMap(e)))
          .toList();
      return Right(data);
    } on ServerException catch (e) {
      return Left(ServerFailure(e.message, e.statusCode));
    }
  }
}
