import 'package:ecomotif/logic/cubit/language_code_state.dart';
import 'package:ecomotif/widgets/fetch_error_text.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../logic/cubit/subscription/subscription_cubit.dart';
import '../../../logic/cubit/subscription/subscription_state.dart';
import '../../../routes/route_names.dart';
import '../../../utils/utils.dart';
import '../../../widgets/custom_text.dart';
import '../../../widgets/loading_widget.dart';
import '../../../widgets/primary_button.dart';

class StripePaymentScreen extends StatefulWidget {
  const StripePaymentScreen({super.key, required this.id});

  final String id;

  @override
  State<StripePaymentScreen> createState() => _StripePaymentScreenState();
}

class _StripePaymentScreenState extends State<StripePaymentScreen> {
  TextEditingController cardNumberController = TextEditingController();
  TextEditingController expireMonthController = TextEditingController();
  TextEditingController expireYearController = TextEditingController();
  TextEditingController cvcController = TextEditingController();

  @override
  Widget build(BuildContext context) {
    const spacer = SizedBox(height: 20.0);

    return Scaffold(
      appBar: AppBar(
        title:
            CustomText(text: Utils.translatedText(context, 'Stripe Payment')),
      ),
      body: ListView(
        padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 16.0),
        children: [
          const SizedBox(height: 40.0),
          BlocBuilder<SubscriptionCubit, LanguageCodeState>(
            builder: (context, state) {
              final payment = state.subscriptionListState;
              return Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  TextFormField(
                    controller: cardNumberController,
                    decoration: InputDecoration(
                      hintText: Utils.translatedText(context, 'Card Number'),
                    ),
                    keyboardType: TextInputType.number,
                    inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                  ),
                  if (payment is StripePaymentFormError) ...[
                    if (payment.errors.cardNumber.isNotEmpty)
                      FetchErrorText(text: payment.errors.cardNumber.first),
                  ]
                ],
              );
            },
          ),
          spacer,
          BlocBuilder<SubscriptionCubit, LanguageCodeState>(
            builder: (context, state) {
              final payment = state.subscriptionListState;
              return Row(
                children: [
                  Expanded(
                      child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      TextFormField(
                        controller: expireMonthController,
                        decoration: InputDecoration(
                            hintText:
                                Utils.translatedText(context, 'Expired Month')),
                        keyboardType: TextInputType.number,
                        inputFormatters: [
                          FilteringTextInputFormatter.digitsOnly
                        ],
                      ),
                      if (payment is StripePaymentFormError) ...[
                        if (payment.errors.cardNumber.isNotEmpty)
                          FetchErrorText(text: payment.errors.cardNumber.first),
                      ]
                    ],
                  )),
                  const SizedBox(width: 14.0),
                  Expanded(
                      child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      TextFormField(
                        controller: expireYearController,
                        decoration: InputDecoration(
                            hintText:
                                Utils.translatedText(context, 'Expired Year')),
                        keyboardType: TextInputType.number,
                        inputFormatters: [
                          FilteringTextInputFormatter.digitsOnly
                        ],
                      ),
                      if (payment is StripePaymentFormError) ...[
                        if (payment.errors.year.isNotEmpty)
                          FetchErrorText(text: payment.errors.year.first),
                      ]
                    ],
                  )),
                ],
              );
            },
          ),
          spacer,
          BlocBuilder<SubscriptionCubit, LanguageCodeState>(
            builder: (context, state) {
              final payment = state.subscriptionListState;
              return Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  TextFormField(
                    controller: cvcController,
                    obscureText: true,
                    decoration: InputDecoration(
                        hintText: Utils.translatedText(context, 'CVV')),
                    keyboardType: TextInputType.number,
                  ),
                  if (payment is StripePaymentFormError) ...[
                    if (payment.errors.cvc.isNotEmpty)
                      FetchErrorText(text: payment.errors.cvc.first),
                  ]
                ],
              );
            },
          ),
          spacer,
          BlocConsumer<SubscriptionCubit, LanguageCodeState>(
            listener: (context, state) {
              final pay = state.subscriptionListState;
              if (pay is StripePaymentStateError) {
                Utils.failureSnackBar(context, pay.message);
              } else if (pay is StripePaymentStateLoaded) {
                Navigator.pushNamedAndRemoveUntil(
                    context, RouteNames.subscriptionScreen, (route) {
                  if (route.settings.name == RouteNames.mainScreen) {
                    debugPrint('setting-value ${route.settings.name}');
                    return true;
                  }
                  return false;
                }, arguments: true);

                Utils.successSnackBar(context, pay.message);
                // Future.delayed(const Duration(milliseconds: 1500), () {
                //
                // });
              }
            },
            builder: (context, storeState) {
              final state = storeState.subscriptionListState;
              if (state is StripePaymentStateLoading) {
                return const LoadingWidget();
              }
              return PrimaryButton(
                  text: Utils.translatedText(context, 'Payment now'),
                  onPressed: () {
                    final body = {
                      'card_number': cardNumberController.text.trim(),
                      'month': expireMonthController.text.trim(),
                      'year': expireYearController.text.trim(),
                      'cvc': cvcController.text.trim(),
                    };
                    context
                        .read<SubscriptionCubit>()
                        .payWithStripe(widget.id, body);
                    cardNumberController.clear();
                    expireMonthController.clear();
                    expireYearController.clear();
                    cvcController.clear();
                  });
            },
          ),
        ],
      ),
    );
  }
}
