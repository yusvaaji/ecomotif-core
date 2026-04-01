import 'package:ecomotif/data/model/subscription/transaction_model.dart';
import 'package:ecomotif/logic/cubit/language_code_state.dart';
import 'package:ecomotif/logic/cubit/subscription/subscription_cubit.dart';
import 'package:ecomotif/logic/cubit/subscription/subscription_state.dart';
import 'package:ecomotif/routes/route_names.dart';
import 'package:ecomotif/utils/k_images.dart';
import 'package:ecomotif/widgets/custom_app_bar.dart';
import 'package:ecomotif/widgets/custom_image.dart';
import 'package:ecomotif/widgets/custom_text.dart';
import 'package:ecomotif/widgets/primary_button.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../utils/constraints.dart';
import '../../../../utils/utils.dart';
import '../../../../widgets/fetch_error_text.dart';
import '../../../../widgets/loading_widget.dart';

class PurchaseHistoryScreen extends StatefulWidget {
  const PurchaseHistoryScreen({super.key});

  @override
  State<PurchaseHistoryScreen> createState() => _PurchaseHistoryScreenState();
}

class _PurchaseHistoryScreenState extends State<PurchaseHistoryScreen> {
  late SubscriptionCubit sCubit;

  @override
  void initState() {
    // TODO: implement initState
    super.initState();
    sCubit = context.read<SubscriptionCubit>();
    sCubit.transactionList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: const CustomAppBar(title: "Purchase History"),
      body: BlocConsumer<SubscriptionCubit, LanguageCodeState>(
          listener: (context, state) {
            final history = state.subscriptionListState;
            if (history is TransactionListStateError) {
              if (history.statusCode == 503 ||
                  sCubit.transactionModel.isNotEmpty) {
                Utils.errorSnackBar(context, history.message);
              }
              if (history.statusCode == 401) {
                Utils.logoutFunction(context);
              }
            }
          },
        builder: (context, state) {
          final history = state.subscriptionListState;
          if (history is TransactionListStateLoading) {
            return const LoadingWidget();
          } else if (history is SubscriptionListStateError) {
            if (history.statusCode == 503 ||
                sCubit.transactionModel.isNotEmpty) {
              return LoadedHistory(data: sCubit.transactionModel);
            } else {
              return FetchErrorText(text: history.message);
            }
          } else if (history is TransactionListStateLoaded) {
            return LoadedHistory(data: sCubit.transactionModel);
          }
          if (sCubit.transactionModel.isNotEmpty) {
            return LoadedHistory(data: sCubit.transactionModel);
          } else {
            return const FetchErrorText(text: 'Something went wrong');
          }
        }
      ),
      bottomNavigationBar: Padding(
        padding: Utils.symmetric(h: 20.0, v: 20.0),
        child: PrimaryButton(text: "Upgrade Plan", onPressed: (){
          Navigator.pushNamed(context, RouteNames.subscriptionScreen);
        }),
      ),
    );
  }
}

class LoadedHistory extends StatelessWidget {
  const LoadedHistory({
    super.key, required this.data,
  });

  final List<TransactionModel> data;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        boxShadow: [
          BoxShadow(
            color: Color(0x0A000012),
            blurRadius: 30,
            offset: Offset(0, 2),
            spreadRadius: 0,
          )
        ],
      ),
      child: Padding(
        padding: Utils.symmetric(),
        child: SingleChildScrollView(
          child: Column(
            children: [
              ...List.generate(data.length, (index) {
                final history = data[index];
                return Padding(
                  padding: Utils.only(bottom: 20.0),
                  child: GestureDetector(
                    onTap: (){
                      showDialog(context: context, builder: (context){
                        return Dialog(
                          backgroundColor: whiteColor,
                          insetPadding: const EdgeInsets.symmetric(horizontal: 14.0),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(4.0),
                          ),
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Container(
                                padding: Utils.symmetric(v: 10.0, h: 20.0),
                                //width: double.infinity,
                                decoration:  BoxDecoration(
                                  color: const Color(0xFFF3F7FC),
                                  borderRadius: BorderRadius.circular(4.0),
                                ),
                                child: Row(

                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const CustomText(text: "Purchase Details", fontSize: 18,fontWeight: FontWeight.w600,textAlign: TextAlign.center,),
                                    IconButton(onPressed: (){
                                      Navigator.pop(context);
                                    }, icon: const Icon(Icons.close,color: redColor,)),
                                  ],
                                ),
                              ),

                              Padding(
                                  padding: Utils.symmetric(v: 10.0),
                                  child:  Column(children: [
                                    buildInfo(title: "Plan",value: history.planName,),
                                    buildInfo(title: "Price",value: history.planPrice,),
                                    buildInfo(title: "Maximum Car",value: history.maxCar,),
                                    buildInfo(title: "Featured Car",value: history.featuredCar,),
                                    buildInfo(title: "Expiration",value: history.expiration,),
                                    buildInfo(title: "Expiated Date",value: history.expirationDate,),
                                    buildInfo(title: "Payment Method",value: history.paymentMethod,),
                                    buildInfo(title: "Transaction ",value: history.transaction,),
                                    buildInfo(title: "Plan Status",value: history.status,),
                                    buildInfo(title: "Payment",value: history.paymentStatus,),
                                  ],)
                              )
                            ],),
                        );
                      });
                    },
                    child: Container(
                      color: whiteColor,
                      padding: Utils.all(value: 18.0),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                           CustomText(
                            text: history.planName,
                            fontSize: 16.0,
                            fontWeight: FontWeight.w500,
                          ),
                          CustomText(
                            text: history.expiration,
                            fontWeight: FontWeight.w500,
                          ),
                          CustomText(
                            text: Utils.formatAmount(context, history.planPrice),
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                            color: blackColor,
                          ),

                          Container(
                            decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(4.0),
                                color: Utils.transactionColor(history)
                            ),
                            child: Padding(
                              padding: Utils.symmetric(h: 12.0, v: 6.0),
                              child: CustomText(
                                text: Utils.transactionText(context, history),
                                color: Utils.transactionTextColor(history),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              })
            ],
          ),
        ),
      ),
    );
  }
}

class buildInfo extends StatelessWidget {
  const buildInfo({
    super.key, required this.title, required this.value,
  });

  final String title;
  final String value;

  @override
  Widget build(BuildContext context) {
    return  Column(
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
          CustomText(text: title,color: const Color(0xFF6B6C6C),),
          Flexible(child: CustomText(text: value,color: Color(0xFF0D274E), fontSize: 14,fontWeight: FontWeight.w500,maxLine: 1,)),
        ],),
        Utils.verticalSpace(10.0),
        Utils.horizontalLine(),
        Utils.verticalSpace(10.0),
      ],
    );
  }
}
