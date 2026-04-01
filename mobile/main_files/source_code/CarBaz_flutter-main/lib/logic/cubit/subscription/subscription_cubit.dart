import 'package:ecomotif/data/model/payment_model/payment_info_model.dart';
import 'package:ecomotif/data/model/subscription/subscription_list_model.dart';
import 'package:ecomotif/logic/cubit/subscription/subscription_state.dart';
import 'package:ecomotif/logic/repository/subscription_repository.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../data/data_provider/remote_url.dart';
import '../../../data/model/subscription/transaction_model.dart';
import '../../../presentation/errors/failure.dart';
import '../../../utils/utils.dart';
import '../../bloc/login/login_bloc.dart';
import '../language_code_state.dart';

class SubscriptionCubit extends Cubit<LanguageCodeState> {
  final SubscriptionRepository _repository;
  final LoginBloc _loginBloc;

  SubscriptionCubit({
    required SubscriptionRepository repository,
    required LoginBloc loginBloc,
  })
      : _repository = repository,
        _loginBloc = loginBloc,
        super(LanguageCodeState());

  List<SubscriptionListModel> subscriptionListModel = [];

  List<TransactionModel> transactionModel = [];

  PaymentInfoModel? paymentInfoModel;




  Future<void> getSubscriptionList() async {
    emit(state.copyWith(subscriptionListState: SubscriptionListStateLoading()));
    final uri = Utils.tokenWithCode(
      RemoteUrls.subscriptionPlanList,
      _loginBloc.userInformation!.accessToken,
      _loginBloc.state.languageCode,
    );
    final result = await _repository.subscriptionPlanList(uri);
    result.fold((failure) {
      final errorState =
      SubscriptionListStateError(failure.message, failure.statusCode);
      emit(state.copyWith(subscriptionListState: errorState));
    }, (success) {
      subscriptionListModel = success;
      final successState = SubscriptionListStateLoaded(success);
      emit(state.copyWith(subscriptionListState: successState));
    });
  }

  Future<void> getPaymentInfo() async {
    emit(state.copyWith(subscriptionListState: PaymentInfoStateLoading()));
    final uri = Utils.tokenWithCode(
      RemoteUrls.paymentInfo,
      _loginBloc.userInformation!.accessToken,
      _loginBloc.state.languageCode,
    );
    final result = await _repository.paymentInfo(uri);
    result.fold((failure) {
      final errorState =
      PaymentInfoStateError(failure.message, failure.statusCode);
      emit(state.copyWith(subscriptionListState: errorState));
    }, (success) {
      paymentInfoModel = success;
      final successState = PaymentInfoStateLoaded(success);
      emit(state.copyWith(subscriptionListState: successState));
    });
  }

  Future<void> payWithStripe(String id, Map<String, dynamic> body) async {
    emit(state.copyWith(subscriptionListState: const StripePaymentStateLoading()));
    final uri = Utils.tokenWithCode(
      RemoteUrls.payWithStripe(id),
      _loginBloc.userInformation!.accessToken,
      _loginBloc.state.languageCode,
    );
    final result = await _repository.payWithStripe(uri, body);

    result.fold((failure) {
      if (failure is InvalidAuthData) {
        final errors = StripePaymentFormError(failure.errors);
        emit(state.copyWith(subscriptionListState: errors));
      } else {
        final errors =
        StripePaymentStateError(failure.message, failure.statusCode);
        emit(state.copyWith(subscriptionListState: errors));
      }
    },
            (success) {
      final successState = StripePaymentStateLoaded(success);
      emit(state.copyWith(subscriptionListState: successState));
    });
  }


  Future<void> payWithBank(String id, Map<String, dynamic> body) async {
    emit(state.copyWith(subscriptionListState: const BankPaymentStateLoading()));
    final uri = Utils.tokenWithCode(
      RemoteUrls.payWithBank(id),
      _loginBloc.userInformation!.accessToken,
      _loginBloc.state.languageCode,
    );
    final result = await _repository.payWithBank(uri, body);

    result.fold((failure) {
      if (failure is InvalidAuthData) {
        final errors = BankPaymentFormError(failure.errors);
        emit(state.copyWith(subscriptionListState: errors));
      } else {
        final errors =
        BankPaymentStateError(failure.message, failure.statusCode);
        emit(state.copyWith(subscriptionListState: errors));
      }
    },
            (success) {
      final successState = BankPaymentStateLoaded(success);
      emit(state.copyWith(subscriptionListState: successState));
    });
  }


  Future<void> freePlanEnroll(String id) async {
    emit(state.copyWith(subscriptionListState: const FreePlanStateLoading()));
    final uri = Utils.tokenWithCode(
      RemoteUrls.freePlanEnroll(id),
      _loginBloc.userInformation!.accessToken,
      _loginBloc.state.languageCode,
    );
    final result = await _repository.freePlanEnroll(id, uri);
    result.fold((failure) {
      final errorState =
      FreePlanStateError(failure.message, failure.statusCode);
      emit(state.copyWith(subscriptionListState: errorState));
    }, (success) {
      final successState = FreePlanStateLoaded(success);
      emit(state.copyWith(subscriptionListState: successState));
    });
  }


  Future<void> transactionList() async {
    emit(state.copyWith(subscriptionListState: const TransactionListStateLoading()));
    final uri = Utils.tokenWithCode(
      RemoteUrls.transactionList,
      _loginBloc.userInformation!.accessToken,
      _loginBloc.state.languageCode,
    );
    final result = await _repository.transactionList(uri);
    result.fold((failure) {
      final errorState =
      TransactionListStateError(failure.message, failure.statusCode);
      emit(state.copyWith(subscriptionListState: errorState));
    }, (success) {
      transactionModel = success;
      final successState = TransactionListStateLoaded(success);
      emit(state.copyWith(subscriptionListState: successState));
    });
  }



  Uri webPaymentInfo(String url) {
    final body = state.copyWith(
      token: _loginBloc.userInformation!.accessToken,
      languageCode: _loginBloc.state.languageCode,
    );
    final uri = Uri.parse(url).replace(queryParameters: body.toMap());
    return uri;
  }

}