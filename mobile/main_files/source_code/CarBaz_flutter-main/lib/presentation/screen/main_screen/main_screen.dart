import 'package:ecomotif/logic/cubit/compare/compare_list_cubit.dart';
import 'package:ecomotif/logic/cubit/kyc/kyc_info_cubit.dart';
import 'package:ecomotif/logic/cubit/profile/profile_cubit.dart';
import 'package:ecomotif/logic/cubit/wishlist/wishlist_cubit.dart';
import 'package:ecomotif/presentation/screen/all_car_screen/all_car_screen.dart';
import 'package:ecomotif/presentation/screen/save_screen/save_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../logic/cubit/all_cars/all_cars_cubit.dart';
import '../../../widgets/exit_dialog.dart';
import '../home/home_screen.dart';
import '../more_screen/more_screen.dart';
import 'component/bottom_navigation_bar.dart';
import 'component/main_controller.dart';

class MainScreen extends StatefulWidget {
  const MainScreen({super.key});

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  final _homeController = MainController();
  late List<Widget> screens;

  @override
  void initState() {
    screens = [
      const HomeScreen(),
      const AllCarScreen(visibleLeading: false,),
      const SaveScreen(),
      const MoreScreen(),
    ];

    super.initState();

    context.read<WishlistCubit>().getWishlist();
    //context.read<ManageCarCubit>().getCarCreateData();
    context.read<KycInfoCubit>().getKycInfo();
    context.read<ProfileCubit>().getProfileInfo();
    context.read<AllCarsCubit>().getSearchAttribute();
    context.read<CompareCubit>().getCompareList();
  }

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: () async {
        showDialog(context: context, builder: (context) => const ExitDialog());
        return true;
      },
      child: Scaffold(
        body: StreamBuilder<int>(
          initialData: 0,
          stream: _homeController.naveListener.stream,
          builder: (context, AsyncSnapshot<int> snapshot) {
            int item = snapshot.data ?? 0;
            return screens[item];
          },
        ),
        bottomNavigationBar: const MyBottomNavigationBar(),
      ),
    );
  }
}
