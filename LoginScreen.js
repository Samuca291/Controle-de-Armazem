import React, { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Dimensions,
  KeyboardAvoidingView,
  Platform,
  SafeAreaView,
  Animated,
  Easing
} from 'react-native';
import Icon from 'react-native-vector-icons/FontAwesome';
import { LinearGradient } from 'expo-linear-gradient';

const { width, height } = Dimensions.get('window');

export default function LoginScreen() {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  const fadeIn = new Animated.Value(0);
  const slideUp = new Animated.Value(50);
  const scale = new Animated.Value(0.3);
  const inputAnimation = new Animated.Value(0);

  const [gradientColors, setGradientColors] = useState(['#1a237e', '#0d47a1', '#283593']);
  const colorAnimation = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    Animated.parallel([
      Animated.timing(fadeIn, {
        toValue: 1,
        duration: 1000,
        useNativeDriver: true,
      }),
      Animated.timing(slideUp, {
        toValue: 0,
        duration: 800,
        useNativeDriver: true,
      }),
      Animated.spring(scale, {
        toValue: 1,
        friction: 8,
        tension: 40,
        useNativeDriver: true,
      })
    ]).start();

    const animateBackground = () => {
      Animated.sequence([
        Animated.timing(colorAnimation, {
          toValue: 1,
          duration: 5000,
          useNativeDriver: false,
        }),
        Animated.timing(colorAnimation, {
          toValue: 0,
          duration: 5000,
          useNativeDriver: false,
        })
      ]).start(() => animateBackground());
    };

    animateBackground();
  }, []);

  const handleInputFocus = () => {
    Animated.timing(inputAnimation, {
      toValue: 1,
      duration: 200,
      useNativeDriver: true,
    }).start();
  };

  const handleInputBlur = () => {
    Animated.timing(inputAnimation, {
      toValue: 0,
      duration: 200,
      useNativeDriver: true,
    }).start();
  };

  const handleLogin = async () => {
    if (!username || !password) {
      alert('Por favor, preencha todos os campos');
      return;
    }
    setIsLoading(true);
    // Implementar lógica de login
    setTimeout(() => setIsLoading(false), 2000);
  };

  const animatedColors = {
    color1: colorAnimation.interpolate({
      inputRange: [0, 0.5, 1],
      outputRange: ['#1a237e', '#283593', '#0d47a1']
    }),
    color2: colorAnimation.interpolate({
      inputRange: [0, 0.5, 1],
      outputRange: ['#0d47a1', '#1a237e', '#283593']
    }),
    color3: colorAnimation.interpolate({
      inputRange: [0, 0.5, 1],
      outputRange: ['#283593', '#0d47a1', '#1a237e']
    })
  };

  return (
    <SafeAreaView style={styles.container}>
      <Animated.View style={[styles.backgroundContainer]}>
        <LinearGradient
          colors={[animatedColors.color1, animatedColors.color2, animatedColors.color3]}
          style={styles.gradient}
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 1 }}
        >
          <View style={styles.particlesContainer}>
            {Array(20).fill().map((_, i) => (
              <Animated.View
                key={i}
                style={[
                  styles.particle,
                  {
                    left: `${Math.random() * 100}%`,
                    top: `${Math.random() * 100}%`,
                    transform: [
                      { scale: new Animated.Value(Math.random()) },
                      { translateY: new Animated.Value(Math.random() * 200) }
                    ]
                  }
                ]}
              />
            ))}
          </View>

          <KeyboardAvoidingView
            behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
            style={styles.content}
          >
            <Animated.View 
              style={[
                styles.logoContainer,
                {
                  opacity: fadeIn,
                  transform: [
                    { translateY: slideUp },
                    { scale }
                  ]
                }
              ]}
            >
              <Text style={styles.logoText}>GESTÃO</Text>
              <Text style={[styles.subLogoText, { marginTop: 12 }]}>DE ESTOQUE</Text>
            </Animated.View>

            <Animated.View 
              style={[
                styles.formContainer,
                {
                  opacity: fadeIn,
                  transform: [
                    { translateY: slideUp },
                    { scale }
                  ]
                }
              ]}
            >
              <Animated.View style={[
                styles.inputContainer,
                {
                  transform: [{
                    scale: inputAnimation.interpolate({
                      inputRange: [0, 1],
                      outputRange: [1, 1.02]
                    })
                  }]
                }
              ]}>
                <Icon name="user" size={20} color="#666" style={styles.icon} />
                <TextInput
                  style={styles.input}
                  placeholder="Usuário"
                  placeholderTextColor="#666"
                  value={username}
                  onChangeText={setUsername}
                  onFocus={handleInputFocus}
                  onBlur={handleInputBlur}
                  autoCapitalize="none"
                />
              </Animated.View>

              <Animated.View style={[
                styles.inputContainer,
                {
                  transform: [{
                    scale: inputAnimation.interpolate({
                      inputRange: [0, 1],
                      outputRange: [1, 1.02]
                    })
                  }]
                }
              ]}>
                <Icon name="lock" size={20} color="#666" style={styles.icon} />
                <TextInput
                  style={styles.input}
                  placeholder="Senha"
                  placeholderTextColor="#666"
                  value={password}
                  onChangeText={setPassword}
                  secureTextEntry={!showPassword}
                  onFocus={handleInputFocus}
                  onBlur={handleInputBlur}
                />
                <TouchableOpacity
                  onPress={() => setShowPassword(!showPassword)}
                  style={styles.eyeIcon}
                >
                  <Icon
                    name={showPassword ? 'eye-slash' : 'eye'}
                    size={20}
                    color="#666"
                  />
                </TouchableOpacity>
              </Animated.View>

              <TouchableOpacity
                style={styles.loginButton}
                onPress={handleLogin}
                activeOpacity={0.7}
              >
                <Text style={styles.loginButtonText}>Entrar</Text>
              </TouchableOpacity>

              <View style={styles.linksContainer}>
                <TouchableOpacity>
                  <Text style={styles.linkText}>Esqueci minha senha</Text>
                </TouchableOpacity>
                <TouchableOpacity>
                  <Text style={styles.linkText}>Registrar-se</Text>
                </TouchableOpacity>
              </View>
            </Animated.View>
          </KeyboardAvoidingView>
        </LinearGradient>
      </Animated.View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  backgroundContainer: {
    flex: 1,
    width: '100%',
    height: '100%',
  },
  gradient: {
    flex: 1,
    width: '100%',
    height: '100%',
  },
  particlesContainer: {
    position: 'absolute',
    width: '100%',
    height: '100%',
    overflow: 'hidden',
  },
  particle: {
    position: 'absolute',
    width: 4,
    height: 4,
    backgroundColor: 'rgba(255, 255, 255, 0.15)',
    borderRadius: 2,
  },
  content: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: width * 0.05,
  },
  logoContainer: {
    alignItems: 'center',
    marginBottom: height * 0.05,
  },
  logoText: {
    fontSize: width * 0.09,
    fontWeight: 'bold',
    color: '#fff',
    letterSpacing: 2,
  },
  subLogoText: {
    fontSize: width * 0.045,
    color: '#fff',
    letterSpacing: 1,
  },
  formContainer: {
    width: '90%',
    maxWidth: 400,
    backgroundColor: 'rgba(255, 255, 255, 0.92)',
    borderRadius: 20,
    padding: width * 0.05,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.3,
    shadowRadius: 4.65,
    elevation: 8,
  },
  inputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#fff',
    borderRadius: 10,
    marginBottom: height * 0.02,
    paddingHorizontal: width * 0.04,
    height: height * 0.06,
    borderWidth: 1,
    borderColor: '#ddd',
  },
  icon: {
    marginRight: 10,
  },
  input: {
    flex: 1,
    fontSize: width * 0.04,
    color: '#333',
    textAlign: 'center',
    backgroundColor: 'rgba(255, 255, 255, 0.95)',
  },
  eyeIcon: {
    padding: 10,
  },
  rememberContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 20,
  },
  checkbox: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  rememberText: {
    marginLeft: 10,
    color: '#666',
    fontSize: 14,
  },
  loginButton: {
    backgroundColor: '#1a237e',
    borderRadius: 10,
    height: height * 0.06,
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: height * 0.02,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
    elevation: 5,
    borderColor: 'rgba(255, 255, 255, 0.2)',
    borderWidth: 1,
  },
  loginButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
    letterSpacing: 1,
  },
  linksContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: 20,
    paddingTop: 20,
    borderTopWidth: 1,
    borderTopColor: '#ddd',
  },
  linkText: {
    color: '#1a237e',
    fontSize: 14,
  },
  '@media (min-width: 768px)': {
    formContainer: {
      width: '70%',
    },
    logoText: {
      fontSize: width * 0.07,
    },
    subLogoText: {
      fontSize: width * 0.035,
    },
  },
  '@media (min-width: 1024px)': {
    formContainer: {
      width: '50%',
    },
    logoText: {
      fontSize: width * 0.05,
    },
    subLogoText: {
      fontSize: width * 0.025,
    },
  },
  glassEffect: {
    backgroundColor: 'rgba(255, 255, 255, 0.1)',
    borderRadius: 16,
    backdropFilter: 'blur(5px)',
    border: '1px solid rgba(255, 255, 255, 0.3)',
  }
}); 