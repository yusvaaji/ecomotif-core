import 'package:ecomotif/data/model/payment_model/payment_info_model.dart';
import 'package:ecomotif/data/model/subscription/subscription_list_model.dart';
import 'package:ecomotif/data/model/subscription/transaction_model.dart';
import 'package:equatable/equatable.dart';

import '../../../presentation/errors/errors_model.dart';


class SubscriptionListState extends Equatable {
  const SubscriptionListState();

  @override
  List<Object> get props => [];
}

class SubscriptionListInitial extends SubscriptionListState {
  const SubscriptionListInitial();

  @override
  List<Object> get props => [];
}

class SubscriptionListStateLoading extends SubscriptionListState {}

class SubscriptionListStateLoaded extends SubscriptionListState {
  final List<SubscriptionListModel> subscriptionListModel;

  const SubscriptionListStateLoaded(this.subscriptionListModel);

  @override
  List<Object> get props => [subscriptionListModel];
}

class SubscriptionListStateError extends SubscriptionListState {
  final String message;
  final int statusCode;

  const SubscriptionListStateError(this.message, this.statusCode);

  @override
  List<Object> get props => [message, statusCode];
}

/// payment Info

class PaymentInfoStateLoading extends SubscriptionListState {}

class PaymentInfoStateLoaded extends SubscriptionListState {
  final PaymentInfoModel paymentInfoModel;

  const PaymentInfoStateLoaded(this.paymentInfoModel);

  @override
  List<Object> get props => [paymentInfoModel];
}

class PaymentInfoStateError extends SubscriptionListState {
  final String message;
  final int statusCode;

  const PaymentInfoStateError(this.message, this.statusCode);

  @override
  List<Object> get props => [message, statusCode];
}

///Stripe payment

final class StripePaymentStateLoading extends SubscriptionListState {
  const StripePaymentStateLoading();
}

final class StripePaymentStateError extends SubscriptionListState {
  final String message;
  final int statusCode;

  const StripePaymentStateError(this.message, this.statusCode);

  @override
  List<Object> get props => [message, statusCode];
}

final class StripePaymentFormError extends SubscriptionListState {
  final Errors errors;

  const StripePaymentFormError(this.errors);

  @override
  List<Object> get props => [errors];
}

final class StripePaymentStateLoaded extends SubscriptionListState {
  final String message;

  const StripePaymentStateLoaded(this.message);

  @override
  List<Object> get props => [message];
}

/// Bank Payment
///
final class BankPaymentStateLoading extends SubscriptionListState {
  const BankPaymentStateLoading();
}

final class BankPaymentStateError extends SubscriptionListState {
  final String message;
  final int statusCode;

  const BankPaymentStateError(this.message, this.statusCode);

  @override
  List<Object> get props => [message, statusCode];
}

final class BankPaymentFormError extends SubscriptionListState {
  final Errors errors;

  const BankPaymentFormError(this.errors);

  @override
  List<Object> get props => [errors];
}

final class BankPaymentStateLoaded extends SubscriptionListState {
  final String message;

  const BankPaymentStateLoaded(this.message);

  @override
  List<Object> get props => [message];
}

/// Free plan Enroll
final class FreePlanStateLoading extends SubscriptionListState {
  const FreePlanStateLoading();
}

final class FreePlanStateError extends SubscriptionListState {
  final String message;
  final int statusCode;

  const FreePlanStateError(this.message, this.statusCode);

  @override
  List<Object> get props => [message, statusCode];
}

final class FreePlanStateLoaded extends SubscriptionListState {
  final String message;

  const FreePlanStateLoaded(this.message);

  @override
  List<Object> get props => [message];
}

/// Transaction List

final class TransactionListStateLoading extends SubscriptionListState {
  const TransactionListStateLoading();
}

final class TransactionListStateError extends SubscriptionListState {
  final String message;
  final int statusCode;

  const TransactionListStateError(this.message, this.statusCode);

  @override
  List<Object> get props => [message, statusCode];
}

final class TransactionListStateLoaded extends SubscriptionListState {
  final List<TransactionModel> transactionModel;

  const TransactionListStateLoaded(this.transactionModel);

  @override
  List<Object> get props => [transactionModel];
}