<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | RS Sehat Selalu</title>
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
                            '0%': { boxShadow: '0 0 20px rgba(59, 130, 246, 0.5)' },
                            '100%': { boxShadow: '0 0 30px rgba(59, 130, 246, 0.8), 0 0 40px rgba(59, 130, 246, 0.3)' },
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
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
        }

        /* Custom focus styles */
        .custom-focus:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
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
            border: 2px solid #3b82f6;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: pulse 2s infinite;
        }

        /* Gradient text effect */
        .gradient-text {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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
<body class="bg-gradient-to-br from-blue-600 via-indigo-700 to-purple-800 min-h-screen font-sans overflow-x-hidden">

    <!-- Floating decorative elements -->
    <div class="fixed inset-0 pointer-events-none z-10">
        <div class="absolute top-20 left-10 w-4 h-4 bg-blue-400 rounded-full opacity-60 floating-particle"></div>
        <div class="absolute top-40 right-20 w-6 h-6 bg-indigo-400 rounded-full opacity-40 floating-particle"></div>
        <div class="absolute bottom-32 left-20 w-3 h-3 bg-purple-400 rounded-full opacity-70 floating-particle"></div>
        <div class="absolute bottom-20 right-10 w-5 h-5 bg-blue-300 rounded-full opacity-50 floating-particle"></div>
        <div class="absolute top-1/2 left-5 w-2 h-2 bg-indigo-300 rounded-full opacity-80 floating-particle"></div>
        <div class="absolute top-1/3 right-5 w-4 h-4 bg-purple-300 rounded-full opacity-60 floating-particle"></div>
    </div>

    <div class="min-h-screen p-4 relative z-20">
        <!-- Welcome Card -->
        <div class="glass rounded-3xl shadow-2xl p-8 mb-8 text-center transform hover:scale-105 transition-all duration-500 animate-fade-in">
            <h1 class="text-5xl font-bold text-white mb-4 drop-shadow-lg">
                <i class="bi bi-shield-check mr-4 medical-cross"></i>Dashboard Admin
            </h1>
            <p class="text-xl text-white opacity-90 animate-slide-up drop-shadow-md">
                Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>! Kelola sistem RS Sehat Selalu
            </p>
            
            <!-- Animated health indicators -->
            <div class="mt-6 flex justify-center space-x-3">
                <div class="pulse-ring w-3 h-3 bg-green-400 rounded-full"></div>
                <div class="pulse-ring w-3 h-3 bg-blue-400 rounded-full" style="animation-delay: 0.5s;"></div>
                <div class="pulse-ring w-3 h-3 bg-purple-400 rounded-full" style="animation-delay: 1s;"></div>
            </div>
        </div>

        <!-- Menu Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Booking Management Card -->
            <a href="admin_kelola_booking.php" class="group">
                <div class="glass rounded-2xl shadow-xl p-8 text-center transform hover:scale-105 hover:-translate-y-2 transition-all duration-500 animate-slide-up border-l-4 border-blue-400">
                    <i class="bi bi-calendar-check text-6xl text-blue-400 mb-6 group-hover:animate-bounce block"></i>
                    <h3 class="text-2xl font-bold text-white mb-4 drop-shadow-lg">Kelola Booking</h3>
                    <p class="text-white opacity-80">Kelola dan atur jadwal booking pasien</p>
                </div>
            </a>

            <!-- Payment Card -->
            <a href="admin_pembayaran.php" class="group">
                <div class="glass rounded-2xl shadow-xl p-8 text-center transform hover:scale-105 hover:-translate-y-2 transition-all duration-500 animate-slide-up border-l-4 border-green-400" style="animation-delay: 0.1s;">
                    <i class="bi bi-credit-card text-6xl text-green-400 mb-6 group-hover:animate-bounce block"></i>
                    <h3 class="text-2xl font-bold text-white mb-4 drop-shadow-lg">Pembayaran</h3>
                    <p class="text-white opacity-80">Kelola pembayaran dan transaksi</p>
                </div>
            </a>

            <!-- Inpatient Card -->
            <a href="admin_rawat_inap.php" class="group">
                <div class="glass rounded-2xl shadow-xl p-8 text-center transform hover:scale-105 hover:-translate-y-2 transition-all duration-500 animate-slide-up border-l-4 border-pink-400" style="animation-delay: 0.2s;">
                    <i class="bi bi-hospital text-6xl text-pink-400 mb-6 group-hover:animate-bounce block"></i>
                    <h3 class="text-2xl font-bold text-white mb-4 drop-shadow-lg">Rawat Inap</h3>
                    <p class="text-white opacity-80">Kelola data pasien rawat inap</p>
                </div>
            </a>

            <!-- Diagnosis History Card -->
            <a href="admin_riwayat_diagnosa.php" class="group">
                <div class="glass rounded-2xl shadow-xl p-8 text-center transform hover:scale-105 hover:-translate-y-2 transition-all duration-500 animate-slide-up border-l-4 border-orange-400" style="animation-delay: 0.3s;">
                    <i class="bi bi-clipboard2-data text-6xl text-orange-400 mb-6 group-hover:animate-bounce block"></i>
                    <h3 class="text-2xl font-bold text-white mb-4 drop-shadow-lg">Riwayat Diagnosa</h3>
                    <p class="text-white opacity-80">Lihat riwayat diagnosa pasien</p>
                </div>
            </a>

            <!-- Logout Card -->
            <a href="logout.php" class="group">
                <div class="glass rounded-2xl shadow-xl p-8 text-center transform hover:scale-105 hover:-translate-y-2 transition-all duration-500 animate-slide-up border-l-4 border-red-400" style="animation-delay: 0.4s;">
                    <i class="bi bi-box-arrow-right text-6xl text-red-400 mb-6 group-hover:animate-bounce block"></i>
                    <h3 class="text-2xl font-bold text-white mb-4 drop-shadow-lg">Logout</h3>
                    <p class="text-white opacity-80">Keluar dari sistem admin</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- 3D Particles -->
    <script src="particles.js"></script>

</body>
</html>
