import 'package:awesome_snackbar_content/awesome_snackbar_content.dart';
import 'package:ecomotif/utils/constraints.dart';
import 'package:ecomotif/widgets/custom_form.dart';
import 'package:ecomotif/widgets/custom_text.dart';
import 'package:ecomotif/widgets/fetch_error_text.dart';
import 'package:ecomotif/widgets/primary_button.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../data/model/auth/login_state_model.dart';
import '../../../logic/bloc/login/login_bloc.dart';
import '../../../routes/route_names.dart';
import '../../../utils/utils.dart';
import '../../../widgets/loading_widget.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  late LoginBloc loginBloc;

  @override
  void initState() {
    loginBloc = context.read<LoginBloc>();
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    return Scaffold(
      backgroundColor: const Color(0xFFEEF2F6),
      body: SingleChildScrollView(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.end,
          children: [
            Utils.verticalSpace(size.height * 0.19),
            Container(
              height: size.height * 0.78,
              decoration: const BoxDecoration(
                borderRadius: BorderRadius.only(
                  topRight: Radius.circular(20.0),
                  topLeft: Radius.circular(20.0),
                ),
                color: whiteColor
              ),
              child: ListView(
                padding: Utils.symmetric(v: 20.0),
                children: [
                  const CustomText(
                    text: "Log in to your Account",
                    fontSize: 24,
                    fontWeight: FontWeight.w500,
                  ),
                  Utils.verticalSpace(16.0),
                  BlocBuilder<LoginBloc, LoginStateModel>(builder: (context, state) {
                    final login = state.loginState;
                    return Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        CustomForm(
                            label: 'Email',
                            child: TextFormField(
                              initialValue: state.email,
                              onChanged: (value) =>
                                  loginBloc.add(LoginEventUserEmail(value)),
                              decoration: const InputDecoration(
                                hintText: 'email here',
                              ),
                              keyboardType: TextInputType.emailAddress,
                            )),
                        if (login is LoginStateFormValidate) ...[
                          if (login.errors.email.isNotEmpty)
                            FetchErrorText(text: login.errors.email.first)
                        ]
                      ],
                    );
                  }),
                  Utils.verticalSpace(10.0),
                  BlocBuilder<LoginBloc, LoginStateModel>(builder: (context, state) {
                    final login = state.loginState;
                    return Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        CustomForm(
                            label: 'Password',
                            child: TextFormField(
                              keyboardType: TextInputType.visiblePassword,
                              initialValue: state.password,
                              onChanged: (value) =>
                                  loginBloc.add(LoginEventPassword(value)),
                              obscureText: state.show,
                              decoration: InputDecoration(
                                hintText: 'Password here',
                                suffixIcon: IconButton(
                                  onPressed: () =>
                                      loginBloc.add(ShowPasswordEvent(state.show)),
                                  icon: Icon(
                                      state.show
                                          ? Icons.visibility_off_outlined
                                          : Icons.visibility_outlined,
                                      color: blackColor),
                                ),
                              ),
                            )),
                      ],
                    );
                  }),
                  Utils.verticalSpace(16.0),
             BlocBuilder<LoginBloc, LoginStateModel>(
                    builder: (context,state) {
                      return Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              Container(
                                margin: Utils.only(right: 8.0),
                                height: Utils.vSize(24.0),
                                width: Utils.hSize(24.0),
                                child: Checkbox(
                                  onChanged: (v) =>
                                      loginBloc.add(LoginEventRememberMe()),
                                  value: state.isActive,
                                  activeColor: primaryColor,
                                ),
                              ),
                              const CustomText(
                                text: 'Remember me',
                                fontSize: 16.0,
                                fontWeight: FontWeight.w400,
                                color: blackColor,
                                height: 1.6,
                              ),
                            ],
                          ),
                          GestureDetector(
                            onTap: () => Navigator.pushNamed(
                                context, RouteNames.forgotPasswordScreen),
                            child: const CustomText(
                              text: 'Forgot Password',
                              fontSize: 16.0,
                              fontWeight: FontWeight.w400,
                              color: Color(0xFF4B83FC),
                            ),
                          ),
                        ],
                      );
                    }
                  ),
                  Utils.verticalSpace(30.0),
                  BlocConsumer<LoginBloc, LoginStateModel>(
                    listener: (context, login) {
                      final state = login.loginState;
                      if (state is LoginStateError) {
                       Utils.failureSnackBar(context, state.message);
                      } else if (state is LoginStateLoaded) {
                        Navigator.pushNamedAndRemoveUntil(
                            context, RouteNames.mainScreen, (route) => false);
                      }
                    },
                    builder: (context, login) {
                      final state = login.loginState;
                      if (state is LoginStateLoading) {
                        return const LoadingWidget();
                      }
                      return PrimaryButton(
                        text: Utils.translatedText(context, 'Log in'),
                        onPressed: () {
                          Utils.closeKeyBoard(context);
                          loginBloc.add(const LoginEventSubmit());
                        },
                      );
                    },
                  ),
                  Utils.verticalSpace(18.0),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const CustomText(
                        text: "Don't have an account? ",
                        fontSize: 16.0,
                        fontWeight: FontWeight.w400,
                        color: blackColor,
                        height: 1.6,
                      ),
                      GestureDetector(
                        onTap: () =>
                            Navigator.pushNamed(context, RouteNames.registrationScreen),
                        child: const CustomText(
                          text: 'Sign up',
                          fontSize: 16.0,
                          fontWeight: FontWeight.w400,
                          fontFamily: bold700,
                          color: primaryColor,
                          height: 1.6,
                        ),
                      ),
                    ],
                  ),
                  Utils.verticalSpace(20.0),
                  GestureDetector(
                    onTap: (){
                      Navigator.pushNamedAndRemoveUntil(
                          context, RouteNames.mainScreen, (route) => false);
                    },
                    child: const CustomText(
                      textAlign: TextAlign.center,
                      text: "Guest Login", fontSize: 16,fontWeight: FontWeight.w500,),
                  )
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
