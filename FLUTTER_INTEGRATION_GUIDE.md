# Flutter Mobile App Integration Guide

## Overview
This guide will help you integrate your Flutter mobile app with the Laravel CRM backend API.

---

## 1. Setup Flutter Project

### Required Packages
Add these to your `pubspec.yaml`:

```yaml
dependencies:
  flutter:
    sdk: flutter
  
  # HTTP & API
  dio: ^5.4.0
  retrofit: ^4.0.3
  json_annotation: ^4.8.1
  
  # State Management
  flutter_bloc: ^8.1.3
  equatable: ^2.0.5
  
  # Local Storage
  flutter_secure_storage: ^9.0.0
  hive: ^2.2.3
  hive_flutter: ^1.1.0
  
  # UI
  cached_network_image: ^3.3.0
  pull_to_refresh: ^2.0.0
  shimmer: ^3.0.0
  
  # Utils
  intl: ^0.18.1
  logger: ^2.0.2

dev_dependencies:
  build_runner: ^2.4.7
  retrofit_generator: ^8.0.4
  json_serializable: ^6.7.1
  hive_generator: ^2.0.1
```

---

## 2. Project Structure

```
lib/
├── core/
│   ├── api/
│   │   ├── api_client.dart
│   │   ├── api_endpoints.dart
│   │   └── interceptors/
│   │       ├── auth_interceptor.dart
│   │       └── logging_interceptor.dart
│   ├── config/
│   │   └── app_config.dart
│   ├── errors/
│   │   ├── failures.dart
│   │   └── exceptions.dart
│   └── utils/
│       ├── token_manager.dart
│       └── dio_client.dart
├── data/
│   ├── models/
│   │   ├── user_model.dart
│   │   ├── lead_model.dart
│   │   ├── person_model.dart
│   │   ├── activity_model.dart
│   │   └── ...
│   ├── repositories/
│   │   ├── auth_repository.dart
│   │   ├── lead_repository.dart
│   │   └── ...
│   └── datasources/
│       ├── remote/
│       │   ├── auth_remote_datasource.dart
│       │   ├── lead_remote_datasource.dart
│       │   └── ...
│       └── local/
│           └── local_storage.dart
├── domain/
│   ├── entities/
│   │   ├── user.dart
│   │   ├── lead.dart
│   │   └── ...
│   └── usecases/
│       ├── login_usecase.dart
│       ├── get_leads_usecase.dart
│       └── ...
├── presentation/
│   ├── screens/
│   │   ├── auth/
│   │   │   ├── login_screen.dart
│   │   │   └── profile_screen.dart
│   │   ├── dashboard/
│   │   │   └── dashboard_screen.dart
│   │   ├── leads/
│   │   │   ├── leads_list_screen.dart
│   │   │   ├── lead_detail_screen.dart
│   │   │   └── create_lead_screen.dart
│   │   └── ...
│   ├── widgets/
│   │   └── common/
│   └── bloc/
│       ├── auth/
│       ├── leads/
│       └── ...
└── main.dart
```

---

## 3. API Configuration

### app_config.dart
```dart
class AppConfig {
  static const String baseUrl = 'http://your-domain.com/api/v1';
  static const int connectionTimeout = 30000; // 30 seconds
  static const int receiveTimeout = 30000;
  
  // For local development
  // static const String baseUrl = 'http://10.0.2.2:8000/api/v1'; // Android Emulator
  // static const String baseUrl = 'http://localhost:8000/api/v1'; // iOS Simulator
}
```

### dio_client.dart
```dart
import 'package:dio/dio.dart';
import '../config/app_config.dart';
import 'interceptors/auth_interceptor.dart';
import 'interceptors/logging_interceptor.dart';

class DioClient {
  static Dio? _dio;

  static Dio get instance {
    if (_dio == null) {
      _dio = Dio(
        BaseOptions(
          baseUrl: AppConfig.baseUrl,
          connectTimeout: Duration(milliseconds: AppConfig.connectionTimeout),
          receiveTimeout: Duration(milliseconds: AppConfig.receiveTimeout),
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
        ),
      );

      // Add interceptors
      _dio!.interceptors.add(AuthInterceptor());
      _dio!.interceptors.add(LoggingInterceptor());
      
      return _dio!;
    }
    return _dio!;
  }
}
```

### auth_interceptor.dart
```dart
import 'package:dio/dio.dart';
import '../utils/token_manager.dart';

class AuthInterceptor extends Interceptor {
  final TokenManager _tokenManager = TokenManager();

  @override
  void onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    final token = await _tokenManager.getToken();
    if (token != null) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    handler.next(options);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) async {
    if (err.response?.statusCode == 401) {
      // Token expired or invalid
      await _tokenManager.deleteToken();
      // Navigate to login screen
      // You can use a navigation service here
    }
    handler.next(err);
  }
}
```

### token_manager.dart
```dart
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class TokenManager {
  static const _storage = FlutterSecureStorage();
  static const _tokenKey = 'auth_token';

  Future<void> saveToken(String token) async {
    await _storage.write(key: _tokenKey, value: token);
  }

  Future<String?> getToken() async {
    return await _storage.read(key: _tokenKey);
  }

  Future<void> deleteToken() async {
    await _storage.delete(key: _tokenKey);
  }

  Future<bool> hasToken() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }
}
```

---

## 4. Data Models

### user_model.dart
```dart
import 'package:json_annotation/json_annotation.dart';

part 'user_model.g.dart';

@JsonSerializable()
class UserModel {
  final int id;
  final String name;
  final String email;
  @JsonKey(name: 'image_url')
  final String? imageUrl;
  final int status;
  @JsonKey(name: 'view_permission')
  final String? viewPermission;
  final RoleModel? role;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    this.imageUrl,
    required this.status,
    this.viewPermission,
    this.role,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) =>
      _$UserModelFromJson(json);

  Map<String, dynamic> toJson() => _$UserModelToJson(this);
}

@JsonSerializable()
class RoleModel {
  final int id;
  final String name;
  @JsonKey(name: 'permission_type')
  final String permissionType;

  RoleModel({
    required this.id,
    required this.name,
    required this.permissionType,
  });

  factory RoleModel.fromJson(Map<String, dynamic> json) =>
      _$RoleModelFromJson(json);

  Map<String, dynamic> toJson() => _$RoleModelToJson(this);
}
```

### lead_model.dart
```dart
import 'package:json_annotation/json_annotation.dart';

part 'lead_model.g.dart';

@JsonSerializable()
class LeadModel {
  final int id;
  final String title;
  final String? description;
  @JsonKey(name: 'lead_value')
  final double? leadValue;
  final String status;
  @JsonKey(name: 'expected_close_date')
  final String? expectedCloseDate;
  @JsonKey(name: 'rotten_days')
  final int rottenDays;
  @JsonKey(name: 'created_at')
  final String createdAt;
  @JsonKey(name: 'updated_at')
  final String updatedAt;
  final UserBasicModel user;
  final PersonBasicModel? person;
  final StageModel stage;
  final PipelineModel pipeline;
  @JsonKey(name: 'products_count')
  final int? productsCount;
  @JsonKey(name: 'activities_count')
  final int? activitiesCount;

  LeadModel({
    required this.id,
    required this.title,
    this.description,
    this.leadValue,
    required this.status,
    this.expectedCloseDate,
    required this.rottenDays,
    required this.createdAt,
    required this.updatedAt,
    required this.user,
    this.person,
    required this.stage,
    required this.pipeline,
    this.productsCount,
    this.activitiesCount,
  });

  factory LeadModel.fromJson(Map<String, dynamic> json) =>
      _$LeadModelFromJson(json);

  Map<String, dynamic> toJson() => _$LeadModelToJson(this);
}

// Run: flutter pub run build_runner build --delete-conflicting-outputs
```

---

## 5. Repository Implementation

### auth_repository.dart
```dart
import 'package:dio/dio.dart';
import '../../core/api/dio_client.dart';
import '../../core/utils/token_manager.dart';
import '../models/user_model.dart';

class AuthRepository {
  final Dio _dio = DioClient.instance;
  final TokenManager _tokenManager = TokenManager();

  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
    String? deviceName,
  }) async {
    try {
      final response = await _dio.post('/login', data: {
        'email': email,
        'password': password,
        'device_name': deviceName ?? 'mobile-app',
      });

      if (response.data['success'] == true) {
        final token = response.data['data']['token'];
        await _tokenManager.saveToken(token);
        
        final user = UserModel.fromJson(response.data['data']['user']);
        
        return {
          'user': user,
          'token': token,
        };
      } else {
        throw Exception('Login failed');
      }
    } on DioException catch (e) {
      if (e.response?.statusCode == 422) {
        throw Exception(e.response?.data['message'] ?? 'Validation error');
      }
      throw Exception('Network error: ${e.message}');
    }
  }

  Future<void> logout() async {
    try {
      await _dio.post('/auth/logout');
      await _tokenManager.deleteToken();
    } catch (e) {
      // Even if API call fails, delete local token
      await _tokenManager.deleteToken();
    }
  }

  Future<UserModel> getProfile() async {
    try {
      final response = await _dio.get('/auth/profile');
      return UserModel.fromJson(response.data['data']);
    } on DioException catch (e) {
      throw Exception('Failed to get profile: ${e.message}');
    }
  }
}
```

### lead_repository.dart
```dart
import 'package:dio/dio.dart';
import '../../core/api/dio_client.dart';
import '../models/lead_model.dart';

class LeadRepository {
  final Dio _dio = DioClient.instance;

  Future<Map<String, dynamic>> getLeads({
    int page = 1,
    int perPage = 15,
    String? search,
    String? status,
  }) async {
    try {
      final response = await _dio.get('/leads', queryParameters: {
        'page': page,
        'per_page': perPage,
        if (search != null) 'search': search,
        if (status != null) 'status': status,
      });

      final leads = (response.data['data'] as List)
          .map((json) => LeadModel.fromJson(json))
          .toList();

      return {
        'leads': leads,
        'meta': response.data['meta'],
      };
    } on DioException catch (e) {
      throw Exception('Failed to get leads: ${e.message}');
    }
  }

  Future<LeadModel> getLeadById(int id) async {
    try {
      final response = await _dio.get('/leads/$id');
      return LeadModel.fromJson(response.data['data']);
    } on DioException catch (e) {
      throw Exception('Failed to get lead: ${e.message}');
    }
  }

  Future<LeadModel> createLead(Map<String, dynamic> data) async {
    try {
      final response = await _dio.post('/leads', data: data);
      return LeadModel.fromJson(response.data['data']);
    } on DioException catch (e) {
      throw Exception('Failed to create lead: ${e.message}');
    }
  }

  Future<LeadModel> updateLead(int id, Map<String, dynamic> data) async {
    try {
      final response = await _dio.put('/leads/$id', data: data);
      return LeadModel.fromJson(response.data['data']);
    } on DioException catch (e) {
      throw Exception('Failed to update lead: ${e.message}');
    }
  }

  Future<void> deleteLead(int id) async {
    try {
      await _dio.delete('/leads/$id');
    } on DioException catch (e) {
      throw Exception('Failed to delete lead: ${e.message}');
    }
  }
}
```

---

## 6. BLoC Implementation Example

### auth_bloc.dart
```dart
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:equatable/equatable.dart';
import '../../data/models/user_model.dart';
import '../../data/repositories/auth_repository.dart';

// Events
abstract class AuthEvent extends Equatable {
  @override
  List<Object?> get props => [];
}

class LoginRequested extends AuthEvent {
  final String email;
  final String password;

  LoginRequested({required this.email, required this.password});

  @override
  List<Object?> get props => [email, password];
}

class LogoutRequested extends AuthEvent {}

class CheckAuthStatus extends AuthEvent {}

// States
abstract class AuthState extends Equatable {
  @override
  List<Object?> get props => [];
}

class AuthInitial extends AuthState {}

class AuthLoading extends AuthState {}

class Authenticated extends AuthState {
  final UserModel user;

  Authenticated(this.user);

  @override
  List<Object?> get props => [user];
}

class Unauthenticated extends AuthState {}

class AuthError extends AuthState {
  final String message;

  AuthError(this.message);

  @override
  List<Object?> get props => [message];
}

// BLoC
class AuthBloc extends Bloc<AuthEvent, AuthState> {
  final AuthRepository authRepository;

  AuthBloc({required this.authRepository}) : super(AuthInitial()) {
    on<LoginRequested>(_onLoginRequested);
    on<LogoutRequested>(_onLogoutRequested);
    on<CheckAuthStatus>(_onCheckAuthStatus);
  }

  Future<void> _onLoginRequested(
    LoginRequested event,
    Emitter<AuthState> emit,
  ) async {
    emit(AuthLoading());
    try {
      final result = await authRepository.login(
        email: event.email,
        password: event.password,
      );
      emit(Authenticated(result['user']));
    } catch (e) {
      emit(AuthError(e.toString()));
    }
  }

  Future<void> _onLogoutRequested(
    LogoutRequested event,
    Emitter<AuthState> emit,
  ) async {
    await authRepository.logout();
    emit(Unauthenticated());
  }

  Future<void> _onCheckAuthStatus(
    CheckAuthStatus event,
    Emitter<AuthState> emit,
  ) async {
    try {
      final user = await authRepository.getProfile();
      emit(Authenticated(user));
    } catch (e) {
      emit(Unauthenticated());
    }
  }
}
```

---

## 7. UI Example - Login Screen

### login_screen.dart
```dart
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../bloc/auth/auth_bloc.dart';

class LoginScreen extends StatefulWidget {
  @override
  _LoginScreenState createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Login')),
      body: BlocConsumer<AuthBloc, AuthState>(
        listener: (context, state) {
          if (state is Authenticated) {
            Navigator.pushReplacementNamed(context, '/dashboard');
          } else if (state is AuthError) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text(state.message)),
            );
          }
        },
        builder: (context, state) {
          if (state is AuthLoading) {
            return Center(child: CircularProgressIndicator());
          }

          return Padding(
            padding: EdgeInsets.all(16.0),
            child: Form(
              key: _formKey,
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  TextFormField(
                    controller: _emailController,
                    decoration: InputDecoration(labelText: 'Email'),
                    keyboardType: TextInputType.emailAddress,
                    validator: (value) {
                      if (value?.isEmpty ?? true) {
                        return 'Please enter your email';
                      }
                      return null;
                    },
                  ),
                  SizedBox(height: 16),
                  TextFormField(
                    controller: _passwordController,
                    decoration: InputDecoration(labelText: 'Password'),
                    obscureText: true,
                    validator: (value) {
                      if (value?.isEmpty ?? true) {
                        return 'Please enter your password';
                      }
                      return null;
                    },
                  ),
                  SizedBox(height: 24),
                  ElevatedButton(
                    onPressed: () {
                      if (_formKey.currentState!.validate()) {
                        context.read<AuthBloc>().add(
                              LoginRequested(
                                email: _emailController.text,
                                password: _passwordController.text,
                              ),
                            );
                      }
                    },
                    child: Text('Login'),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }
}
```

---

## 8. Testing

### Example API Test
```dart
void main() {
  test('Login API test', () async {
    final authRepo = AuthRepository();
    
    try {
      final result = await authRepo.login(
        email: 'admin@example.com',
        password: 'admin123',
      );
      
      expect(result['user'], isNotNull);
      expect(result['token'], isNotNull);
      print('Login successful!');
    } catch (e) {
      print('Login failed: $e');
    }
  });
}
```

---

## 9. Common Issues & Solutions

### Issue 1: CORS Error
**Solution**: Ensure your Laravel backend has CORS properly configured in `config/cors.php`

### Issue 2: Connection Refused (Android Emulator)
**Solution**: Use `http://10.0.2.2:8000` instead of `http://localhost:8000`

### Issue 3: SSL Certificate Error
**Solution**: For development, you can bypass SSL (NOT for production):
```dart
(_dio.httpClientAdapter as DefaultHttpClientAdapter).onHttpClientCreate = 
  (client) {
    client.badCertificateCallback = 
      (X509Certificate cert, String host, int port) => true;
    return client;
  };
```

---

## 10. Next Steps

1. ✅ Implement authentication flow
2. ✅ Create dashboard with statistics
3. ✅ Implement leads management
4. ⬜ Add pull-to-refresh
5. ⬜ Implement offline caching
6. ⬜ Add push notifications
7. ⬜ Implement file upload for profile images
8. ⬜ Add search and filters
9. ⬜ Implement pagination
10. ⬜ Add unit and widget tests

---

## Support & Resources

- **Laravel Sanctum Docs**: https://laravel.com/docs/sanctum
- **Flutter Dio**: https://pub.dev/packages/dio
- **Flutter BLoC**: https://bloclibrary.dev

Happy coding! 🚀

