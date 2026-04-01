import 'package:ecomotif/logic/cubit/subscription/subscription_cubit.dart';
import 'package:ecomotif/logic/cubit/subscription/subscription_state.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../logic/cubit/language_code_state.dart';

import '../../../routes/route_names.dart';
import '../../../utils/utils.dart';
import '../../../widgets/custom_text.dart';
import '../../../widgets/fetch_error_text.dart';
import '../../../widgets/loading_widget.dart';
import '../../../widgets/primary_button.dart';


class BankPaymentScreen extends StatefulWidget {
  const BankPaymentScreen({super.key, required this.id});

  final String id;

  @override
  State<BankPaymentScreen> createState() => _BankPaymentScreenState();
}

class _BankPaymentScreenState extends State<BankPaymentScreen> {

  late SubscriptionCubit sCubit;

  TextEditingController tnxInfoController = TextEditingController();

  @override
  void initState() {
    // TODO: implement initState
    super.initState();
    sCubit = context.read<SubscriptionCubit>();
  }
  // CouponDto? couponDto;
  // String couponSlug = "";

  @override
  // void initState() {
  //   getCouponDiscount();
  //   super.initState();
  // }

  // @override
  // void dispose() {
  //   context.read<PaymentCubit>().transactionController.dispose();
  //   super.dispose();
  // }

  // void getCouponDiscount() {
  //   final langCode = context.read<LoginBloc>().state.languageCode;
  //   print('languageCode $langCode');
  //   couponDto = context.read<AddToCartCubit>().couponDto;
  //   if (couponDto != null) {
  //     debugPrint('couponDto $couponDto');
  //     couponSlug =
  //         "&coupon_code=${couponDto!.couponName}&coupon_amount=${couponDto!.discount}&lang_code=$langCode";
  //   }
  // }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      // appBar: const CustomAppBar(
      //   title: 'Bank Payment',
      //   bgColor: primaryColor,
      //   iconColor: whiteColor,
      //   textColor: whiteColor,
      // ),
      appBar: AppBar(
        title:  CustomText(text: Utils.translatedText(context, 'Bank Payment')),
      ),
      body: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20.0),
        child: Column(
          children: [
            Utils.verticalSpace(25.0),
            CustomText(text: sCubit.paymentInfoModel!.bank!.accountInfo),
            Utils.verticalSpace(25.0),
            BlocBuilder<SubscriptionCubit, LanguageCodeState>(
              builder: (context, state) {
                final payment = state.subscriptionListState;
                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    TextFormField(
                      controller: tnxInfoController,
                      maxLines: 5,
                      decoration:  InputDecoration(
                          hintText: Utils.translatedText(context, 'Account Information')),
                    ),
                    if (payment is BankPaymentFormError) ...[
                      if (payment.errors.tnxInfo.isNotEmpty)
                        FetchErrorText(text: payment.errors.tnxInfo.first),
                    ]

                  ],
                );
              },
            ),
            const SizedBox(height: 20.0),
            BlocConsumer<SubscriptionCubit, LanguageCodeState>(
              listener: (context, storeState) {
                final state = storeState.subscriptionListState;
                if (state is BankPaymentStateError) {
                  Utils.failureSnackBar(context, state.message);
                } else if (state is BankPaymentStateLoaded) {
                  Navigator.pushNamed(context, RouteNames.subscriptionScreen);
                  Utils.successSnackBar(context, state.message);
                }
              },
              builder: (context, storeState) {
                final state = storeState.subscriptionListState;
                if (state is BankPaymentStateLoading) {
                  return const LoadingWidget();
                }
                return PrimaryButton(
                    text: Utils.translatedText(context, 'Pay Now'),
                    onPressed: () {
                      final body = {
                        'tnx_info': tnxInfoController.text.trim(),
                      };
                      sCubit.payWithBank(widget.id, body);
                      tnxInfoController.clear();
                    });
              },
            ),
          ],
        ),
      ),
    );
  }
}
