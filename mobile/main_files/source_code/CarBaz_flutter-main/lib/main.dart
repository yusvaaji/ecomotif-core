import 'package:ecomotif/routes/route_names.dart';
import 'package:ecomotif/utils/constraints.dart';
import 'package:ecomotif/utils/k_string.dart';
import 'package:ecomotif/widgets/custom_theme.dart';
import 'package:ecomotif/widgets/fetch_error_text.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

import 'dependency_injection.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);
  await DInjector.initDB();
  runApp(const MainApp());
}

class MainApp extends StatelessWidget {
  const MainApp({super.key});

  @override
  Widget build(BuildContext context) {
    return ScreenUtilInit(
      designSize: const Size(375.0, 812.0),
      minTextAdapt: true,
      splitScreenMode: true,
      useInheritedMediaQuery: true,
      builder: (BuildContext context, child) {
        return MultiRepositoryProvider(
          providers: DInjector.repositoryProvider,
          child: MultiBlocProvider(
            providers: DInjector.blocProviders,
            child: MaterialApp(
              debugShowCheckedModeBanner: false,
              title: KString.appName,
              initialRoute: RouteNames.splashScreen,
              onGenerateRoute: RouteNames.generateRoutes,
              onUnknownRoute: (RouteSettings settings) {
                return MaterialPageRoute(
                  builder: (_) => Scaffold(
                    body: FetchErrorText(
                        text: 'No route defined for ${settings.name}',
                        textColor: redColor),
                  ),
                );
              },
              theme: MyTheme.theme,
            ),
          ),
        );
      },
    );
  }
}
