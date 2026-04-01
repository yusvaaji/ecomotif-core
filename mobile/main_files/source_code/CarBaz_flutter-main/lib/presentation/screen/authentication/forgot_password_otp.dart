import 'package:ecomotif/data/model/auth/register_state_model.dart';
import 'package:ecomotif/logic/cubit/forgot_password/forgot_password_cubit.dart';
import 'package:ecomotif/logic/cubit/register/register_cubit.dart';
import 'package:ecomotif/routes/route_names.dart';
import 'package:ecomotif/utils/constraints.dart';
import 'package:ecomotif/widgets/custom_app_bar.dart';
import 'package:ecomotif/widgets/custom_image.dart';
import 'package:ecomotif/widgets/custom_text.dart';
import 'package:ecomotif/widgets/loading_widget.dart';
import 'package:ecomotif/widgets/primary_button.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:pinput/pinput.dart';

import '../../../logic/cubit/forgot_password/forgot_password_state_model.dart';
import '../../../logic/cubit/register/register_state.dart';
import '../../../utils/k_images.dart';
import '../../../utils/utils.dart';

class ForgotPasswordOtpScreen extends StatefulWidget {
  const ForgotPasswordOtpScreen({super.key});

  @override
  State<ForgotPasswordOtpScreen> createState() =>
      _ForgotPasswordOtpScreenState();
}

class _ForgotPasswordOtpScreenState extends State<ForgotPasswordOtpScreen> {
  late ForgotPasswordCubit rCubit;

  @override
  void initState() {
    super.initState();
    rCubit = context.read<ForgotPasswordCubit>();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: const CustomAppBar(
        title: "",
        visibleLeading: true,
      ),
      body: BlocListener<ForgotPasswordCubit, PasswordStateModel>(
        listener: (context, state) {
          final otp = state.passwordState;
          if (otp is VerifyingForgotPasswordError) {
            Utils.failureSnackBar(context, otp.message);
          } else if (otp is VerifyingForgotPasswordLoaded) {
            Utils.successSnackBar(context, otp.message);
            // signUp.clearState();
            Navigator.pushNamed(context, RouteNames.newPasswordScreen);
          }
        },
        child: Padding(
          padding: Utils.symmetric(),
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                Utils.verticalSpace(20.0),
                const CustomImage(
                  path: KImages.otpImage,
                  height: 120,
                  width: 160,
                ),
                Utils.verticalSpace(50.0),
                const CustomText(
                    text: "Check your Email",
                    fontSize: 24.0,
                    fontWeight: FontWeight.w500),
                Utils.verticalSpace(26.0),
                const CustomText(
                  text:
                      "You should soon receive an email allowing you to send your mail. Please make sure to check your spam and trash if you can't find the email.",
                  color: sTextColor,
                  textAlign: TextAlign.center,
                ),
                Utils.verticalSpace(30.0),
                Pinput(
                  length: 6,
                  defaultPinTheme: PinTheme(
                    height: Utils.vSize(60.0),
                    width: Utils.hSize(60.0),
                    textStyle: GoogleFonts.roboto(
                        fontSize: 24.0, fontWeight: FontWeight.w700),
                    decoration: BoxDecoration(
                        color: Colors.white,
                        border: Border.all(color: borderColor),
                        borderRadius: BorderRadius.circular(10.0)),
                  ),
                  onChanged: (String code) {
                    rCubit.changeCode(code);
                  },
                  onCompleted: (String code) {
                    if (code.length == 6) {
                      rCubit.forgotOtpVerify();
                    } else {
                      Utils.errorSnackBar(context, "enter 6 digit");
                    }
                  },
                ),
                Utils.verticalSpace(40.0),
                BlocBuilder<ForgotPasswordCubit, PasswordStateModel>(
                    builder: (context, state) {
                  if (state is VerifyingForgotPasswordLoading) {
                    return const LoadingWidget();
                  }
                  return PrimaryButton(
                      text: 'Send Code',
                      onPressed: () {
                        rCubit.forgotOtpVerify();
                      });
                }),
                Utils.verticalSpace(30.0),
                const CustomText(
                  text: "Did’t receive the email?",
                  fontSize: 20.0,
                ),
                GestureDetector(
                    onTap: () {},
                    child: const CustomText(
                      text: "Click to resend",
                      fontSize: 20.0,
                      color: textColor,
                    )),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
