import 'dart:developer';

import 'package:ecomotif/data/model/payment_model/payment_info_model.dart';
import 'package:ecomotif/logic/cubit/subscription/subscription_cubit.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../data/data_provider/remote_url.dart';
import '../../../logic/cubit/language_code_state.dart';
import '../../../logic/cubit/subscription/subscription_state.dart';
import '../../../routes/route_names.dart';
import '../../../utils/utils.dart';
import '../../../widgets/custom_app_bar.dart';
import '../../../widgets/custom_image.dart';
import '../../../widgets/custom_text.dart';
import '../../../widgets/fetch_error_text.dart';
import '../../../widgets/loading_widget.dart';

class PaymentScreen extends StatefulWidget {
  const PaymentScreen({
    super.key,
    required this.id,
  });

  final String id;

  @override
  State<PaymentScreen> createState() => _PaymentScreenState();
}

class _PaymentScreenState extends State<PaymentScreen> {
  @override
  void initState() {
    Future.microtask(() {
      context.read<SubscriptionCubit>().getPaymentInfo();
    });
    super.initState();
  }

  @override
  Widget build(BuildContext context) {

    final paymentCubit = context.read<SubscriptionCubit>();

    return Scaffold(
      appBar:
          CustomAppBar(title: Utils.translatedText(context, 'Payment Method')),
      body: BlocConsumer<SubscriptionCubit, LanguageCodeState>(
          listener: (context, state) {
        final payment = state.subscriptionListState;
        if (payment is PaymentInfoStateError) {
          if (payment.statusCode == 503 ||
              paymentCubit.paymentInfoModel == null) {
            Utils.errorSnackBar(context, payment.message);
          }
        }
      }, builder: (context, state) {
        final payment = state.subscriptionListState;
        if (payment is PaymentInfoStateLoading) {
          return const LoadingWidget();
        } else if (payment is PaymentInfoStateError) {
          if (payment.statusCode == 503) {
            if (paymentCubit.paymentInfoModel != null) {
              return PaymentInfoLoadedWidget(
                payment: paymentCubit.paymentInfoModel,
                id: widget.id,
              );
            }
          } else {
            return FetchErrorText(text: payment.message);
          }
        } else if (payment is PaymentInfoStateLoaded) {
          return PaymentInfoLoadedWidget(
            payment: paymentCubit.paymentInfoModel,
            id: widget.id,
          );
        }if(paymentCubit.paymentInfoModel != null){
          return PaymentInfoLoadedWidget(
            payment: paymentCubit.paymentInfoModel,
            id: widget.id,
          );
        }
        return FetchErrorText(
            text: Utils.translatedText(context, 'Something went wrong'));
      }),
    );
  }
}

class PaymentInfoLoadedWidget extends StatelessWidget {
  const PaymentInfoLoadedWidget({super.key, required this.payment, required this.id});

  final PaymentInfoModel? payment;
  final String id;

  @override
  Widget build(BuildContext context) {

    final pCubit = context.read<SubscriptionCubit>();
    return Column(
      children: [
        Padding(
          padding: Utils.symmetric(v: 20.0, h: 0.0),
          child: CustomText(
            text: Utils.translatedText(
                context, 'Select the payment method you want to use'),
            fontWeight: FontWeight.w500,
            fontSize: 16.0,
          ),
        ),
        Expanded(
          child: GridView(
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 1,
                mainAxisSpacing: 10,
                crossAxisSpacing: 10,
                childAspectRatio: 4.0),
            padding: Utils.symmetric(),
            children: [
              payment!.stripe!.status == 1
                  ? SinglePaymentCard(
                      onTap: () {
                        Navigator.pushNamed(context, RouteNames.stripePaymentScreen,arguments:id.toString());
                      },
                      icon: RemoteUrls.imageUrl(payment!.stripe!.image),
                    )
                  : const SizedBox.shrink(),

              payment!.paypal!.status == 1
                  ? SinglePaymentCard(
                      onTap: () {
                        final url = pCubit.webPaymentInfo(RemoteUrls.payWithPaypal(id));
                             log('url $url');
                             Navigator.pushNamed(context, RouteNames.paypalPaymentScreen,
                                 arguments: url.toString());
                      },
                      icon: RemoteUrls.imageUrl(payment!.paypal!.image),
                    )
                  : const SizedBox.shrink(),

              payment!.razorpay!.status == 1
                  ? SinglePaymentCard(
                      onTap: () {
                        final url = pCubit.webPaymentInfo(RemoteUrls.payWithRazorpay(id));
                             log('url $url');
                             Navigator.pushNamed(context, RouteNames.razorpayPaymentScreen,
                                 arguments: url.toString());
                      },
                      icon: RemoteUrls.imageUrl(payment!.razorpay!.image),
                    )
                  : const SizedBox.shrink(),

              payment!.flutterwave!.status == 1
                  ? SinglePaymentCard(
                      onTap: () {
                        final url = pCubit.webPaymentInfo(RemoteUrls.payWithFlutterWave(id));
                        print('url $url');
                        Navigator.pushNamed(context, RouteNames.flutterWavePaymentScreen,
                            arguments: url.toString());
                      },
                      icon: RemoteUrls.imageUrl(payment!.flutterwave!.logo),
                    )
                  : const SizedBox.shrink(),

              payment!.paystack!.paystackStatus == 1
                  ? SinglePaymentCard(
                      onTap: () {
                        final url = pCubit.webPaymentInfo(RemoteUrls.payWithPayStack(id));
                        print('url $url');
                        Navigator.pushNamed(context, RouteNames.payStackPaymentScreen,
                            arguments: url.toString());
                      },
                      icon:
                          RemoteUrls.imageUrl(payment!.paystack!.paystackImage),
                    )
                  : const SizedBox.shrink(),

              payment!.mollie!.mollieStatus == 1
                  ? SinglePaymentCard(
                      onTap: () {
                        final url = pCubit.webPaymentInfo(RemoteUrls.payWithMollie(id));
                        print('url $url');
                        Navigator.pushNamed(context, RouteNames.molliePaymentScreen,
                            arguments: url.toString());
                      },
                      icon: RemoteUrls.imageUrl(payment!.mollie!.mollieImage),
                    )
                  : const SizedBox.shrink(),

              payment!.instamojo!.status == 1
                  ? SinglePaymentCard(
                      onTap: () {
                        final url = pCubit.webPaymentInfo(RemoteUrls.payWithInstamojo(id));
                        print('url $url');
                        Navigator.pushNamed(context, RouteNames.instamojoPaymentScreen,
                            arguments: url.toString());
                      },
                      icon: RemoteUrls.imageUrl(payment!.instamojo!.image),
                    )
                  : const SizedBox.shrink(),

              payment!.bank!.status == 1
                  ? SinglePaymentCard(
                      onTap: () {
                        Navigator.pushNamed(
                            context, RouteNames.bankTransferPaymentScreen, arguments: id.toString());
                      },
                      icon: RemoteUrls.imageUrl(payment!.bank!.image),
                    )
                  : const SizedBox.shrink(),

              Utils.verticalSpace(10.0),

            ],
          ),
        ),
      ],
    );
  }
}

class SinglePaymentCard extends StatelessWidget {
  const SinglePaymentCard({super.key, required this.onTap, required this.icon});

  final VoidCallback onTap;
  final String icon;

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    return GestureDetector(
      onTap: onTap,
      child: Container(
        height: 50,
        alignment: Alignment.center,
        //padding: Utils.symmetric(v: 18.0, h: 0.0),
        // margin: Utils.symmetric(v: 12.0, h: 0.0).copyWith(bottom: 0.0),
        decoration: BoxDecoration(
          border: Border.all(color: const Color(0xFFDBDBDB)),
          borderRadius: Utils.borderRadius(),
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(10.0),
          child: CustomImage(
            path: icon,
            fit: BoxFit.fill,
            height: 30.0,
            width: 130.0,
          ),
        ),
      ),
    );
  }
}

// class PaymentSuccessDialog extends StatelessWidget {
//   const PaymentSuccessDialog({
//     super.key,
//   });
//
//   @override
//   Widget build(BuildContext context) {
//     return FeedBackDialog(
//       image: KImages.checkIcon,
//       message: "Payment Success",
//       child: Column(
//         mainAxisSize: MainAxisSize.min,
//         children: [
//           const Text(
//             'Payment for Plumber successfully done',
//             textAlign: TextAlign.center,
//             style: TextStyle(
//               color: Color(0xFF535769),
//               fontSize: 14,
//               fontFamily: 'Work Sans',
//               fontWeight: FontWeight.w500,
//               height: 1.43,
//             ),
//           ),
//           Utils.verticalSpace(24),
//           PrimaryButton(
//               text: "Back To Home",
//               onPressed: () {
//                 Navigator.pushNamedAndRemoveUntil(
//                     context, RouteNames.mainScreen, (route) => false);
//               })
//         ],
//       ),
//     );
//   }
// }
