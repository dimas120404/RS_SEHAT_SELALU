<?php
session_start();
if ($_SESSION['role'] != 'dokter') {
    header("Location: index.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dokter | RS Sehat Selalu</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'bounce-slow': 'bounce 2s infinite',
                        'fade-in': 'fadeIn 0.8s ease-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'glow': 'glow 2s ease-in-out infinite alternate',
                        'shimmer': 'shimmer 2s linear infinite',
                        'heartbeat': 'heartbeat 1.5s ease-in-out infinite',
                        'wave': 'wave 2s ease-in-out infinite',
                        'rotate-slow': 'rotate 20s linear infinite',
                        'scale-pulse': 'scalePulse 2s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        glow: {
                            '0%': { boxShadow: '0 0 20px rgba(239, 68, 68, 0.5)' },
                            '100%': { boxShadow: '0 0 30px rgba(239, 68, 68, 0.8), 0 0 40px rgba(239, 68, 68, 0.3)' },
                        },
                        shimmer: {
                            '0%': { backgroundPosition: '-200% 0' },
                            '100%': { backgroundPosition: '200% 0' },
                        },
                        heartbeat: {
                            '0%, 100%': { transform: 'scale(1)' },
                            '50%': { transform: 'scale(1.05)' },
                        },
                        wave: {
                            '0%, 100%': { transform: 'rotate(0deg)' },
                            '25%': { transform: 'rotate(5deg)' },
                            '75%': { transform: 'rotate(-5deg)' },
                        },
                        scalePulse: {
                            '0%, 100%': { transform: 'scale(1)' },
                            '50%': { transform: 'scale(1.02)' },
                        }
                    },
                    backgroundImage: {
                        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                        'gradient-conic': 'conic-gradient(from 180deg at 50% 50%, var(--tw-gradient-stops))',
                        'shimmer': 'linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent)',
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
        }

        /* Custom focus styles */
        .custom-focus:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.3);
        }

        /* Glass morphism effect */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Enhanced button hover effect */
        .btn-hover-effect {
            position: relative;
            overflow: hidden;
        }

        .btn-hover-effect::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-hover-effect:hover::before {
            left: 100%;
        }

        /* Floating animation for particles */
        .floating-particle {
            animation: float 4s ease-in-out infinite;
        }

        .floating-particle:nth-child(2n) {
            animation-delay: -2s;
        }

        .floating-particle:nth-child(3n) {
            animation-delay: -1s;
        }

        /* Medical cross animation */
        .medical-cross {
            animation: heartbeat 2s ease-in-out infinite;
        }

        /* Pulse ring effect */
        .pulse-ring {
            position: relative;
        }

        .pulse-ring::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            border: 2px solid #ef4444;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: pulse 2s infinite;
        }

        /* Gradient text effect */
        .gradient-text {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Shimmer loading effect */
        .shimmer-bg {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-red-500 via-pink-600 to-orange-500 min-h-screen font-sans overflow-x-hidden">

    <!-- Floating decorative elements -->
    <div class="fixed inset-0 pointer-events-none z-10">
        <div class="absolute top-20 left-10 w-4 h-4 bg-red-400 rounded-full opacity-60 floating-particle"></div>
        <div class="absolute top-40 right-20 w-6 h-6 bg-pink-400 rounded-full opacity-40 floating-particle"></div>
        <div class="absolute bottom-32 left-20 w-3 h-3 bg-orange-400 rounded-full opacity-70 floating-particle"></div>
        <div class="absolute bottom-20 right-10 w-5 h-5 bg-red-300 rounded-full opacity-50 floating-particle"></div>
        <div class="absolute top-1/2 left-5 w-2 h-2 bg-pink-300 rounded-full opacity-80 floating-particle"></div>
        <div class="absolute top-1/3 right-5 w-4 h-4 bg-orange-300 rounded-full opacity-60 floating-particle"></div>
    </div>

    <div class="min-h-screen flex items-center justify-center p-4 relative z-20">
        <div class="w-full max-w-2xl">
            <div class="glass rounded-3xl shadow-2xl overflow-hidden transform hover:scale-105 transition-all duration-500">
                <!-- Header Section -->
                <div class="bg-gradient-to-br from-red-400 to-orange-500 relative overflow-hidden p-8 text-center">
                    <div class="absolute inset-0 bg-black opacity-20"></div>
                    
                    <!-- Animated medical symbols -->
                    <div class="absolute top-4 left-4 text-white text-2xl medical-cross animate-pulse-slow">
                        <i class="bi bi-heart-pulse"></i>
                    </div>
                    <div class="absolute top-4 right-4 text-white text-xl animate-bounce-slow">
                        <i class="bi bi-stethoscope"></i>
                    </div>
                    <div class="absolute bottom-4 left-4 text-white text-lg animate-wave">
                        <i class="bi bi-activity"></i>
                    </div>
                    <div class="absolute bottom-4 right-4 text-white text-lg animate-rotate-slow">
                        <i class="bi bi-cross-circle"></i>
                    </div>
                    
                    <div class="relative z-10">
                        <h2 class="text-4xl font-bold text-white mb-3 animate-fade-in drop-shadow-lg">
                            <i class="bi bi-heart-pulse mr-3"></i>Dashboard Dokter
                        </h2>
                        <p class="text-lg text-white opacity-90 animate-slide-up drop-shadow-md">
                            Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                        </p>
                        
                        <!-- Animated health indicators -->
                        <div class="mt-6 flex justify-center space-x-3">
                            <div class="pulse-ring w-2 h-2 bg-green-400 rounded-full"></div>
                            <div class="pulse-ring w-2 h-2 bg-red-400 rounded-full" style="animation-delay: 0.5s;"></div>
                            <div class="pulse-ring w-2 h-2 bg-orange-400 rounded-full" style="animation-delay: 1s;"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Menu Section -->
                <div class="p-8 bg-white/95 backdrop-blur-xl">
                    <div class="space-y-4">
                        <!-- Booking Menu -->
                        <a href="dokter_lihat_booking.php" class="group block">
                            <div class="glass rounded-2xl shadow-xl p-6 transform hover:scale-105 hover:-translate-y-2 transition-all duration-500 animate-slide-up border-l-4 border-blue-400 hover:bg-gradient-to-r hover:from-blue-500 hover:to-cyan-500 hover:text-white">
                                <div class="flex items-center space-x-4">
                                    <i class="bi bi-calendar-check text-3xl text-blue-400 group-hover:text-white group-hover:animate-bounce"></i>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-800 group-hover:text-white">Lihat Booking Pasien</h3>
                                        <p class="text-gray-600 group-hover:text-white opacity-80">Kelola janji temu pasien</p>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <!-- Examination Menu -->
                        <a href="dokter_catat_pemeriksaan.php" class="group block">
                            <div class="glass rounded-2xl shadow-xl p-6 transform hover:scale-105 hover:-translate-y-2 transition-all duration-500 animate-slide-up border-l-4 border-green-400 hover:bg-gradient-to-r hover:from-green-500 hover:to-emerald-500 hover:text-white" style="animation-delay: 0.1s;">
                                <div class="flex items-center space-x-4">
                                    <i class="bi bi-clipboard2-pulse text-3xl text-green-400 group-hover:text-white group-hover:animate-bounce"></i>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-800 group-hover:text-white">Catat Pemeriksaan</h3>
                                        <p class="text-gray-600 group-hover:text-white opacity-80">Catat hasil pemeriksaan pasien</p>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <!-- Inpatient Menu -->
                        <a href="dokter_rawat_inap.php" class="group block">
                            <div class="glass rounded-2xl shadow-xl p-6 transform hover:scale-105 hover:-translate-y-2 transition-all duration-500 animate-slide-up border-l-4 border-pink-400 hover:bg-gradient-to-r hover:from-pink-500 hover:to-rose-500 hover:text-white" style="animation-delay: 0.2s;">
                                <div class="flex items-center space-x-4">
                                    <i class="bi bi-hospital text-3xl text-pink-400 group-hover:text-white group-hover:animate-bounce"></i>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-800 group-hover:text-white">Rawat Inap</h3>
                                        <p class="text-gray-600 group-hover:text-white opacity-80">Kelola pasien rawat inap</p>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <!-- Logout Menu -->
                        <a href="logout.php" class="group block">
                            <div class="glass rounded-2xl shadow-xl p-6 transform hover:scale-105 hover:-translate-y-2 transition-all duration-500 animate-slide-up border-l-4 border-red-400 hover:bg-gradient-to-r hover:from-red-500 hover:to-orange-500 hover:text-white" style="animation-delay: 0.3s;">
                                <div class="flex items-center space-x-4">
                                    <i class="bi bi-box-arrow-right text-3xl text-red-400 group-hover:text-white group-hover:animate-bounce"></i>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-800 group-hover:text-white">Logout</h3>
                                        <p class="text-gray-600 group-hover:text-white opacity-80">Keluar dari sistem</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Decorative elements -->
                    <div class="mt-8 flex justify-center space-x-2">
                        <div class="w-2 h-2 bg-red-400 rounded-full animate-pulse"></div>
                        <div class="w-2 h-2 bg-pink-400 rounded-full animate-pulse" style="animation-delay: 0.2s;"></div>
                        <div class="w-2 h-2 bg-orange-400 rounded-full animate-pulse" style="animation-delay: 0.4s;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- 3D Particles -->
    <script src="particles.js"></script>
</body>
</html>
