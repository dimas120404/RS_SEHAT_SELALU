// 3D Health-themed Particles System
// Compatible with all pages - non-intrusive background effect

(function() {
    'use strict';
    
    // Check if Three.js is loaded
    if (typeof THREE === 'undefined') {
        console.warn('Three.js not loaded. Loading from CDN...');
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js';
        script.onload = initParticles;
        document.head.appendChild(script);
    } else {
        initParticles();
    }
    
    function initParticles() {
        // Create particles container if it doesn't exist
        let particlesContainer = document.getElementById('particles-container');
        if (!particlesContainer) {
            particlesContainer = document.createElement('div');
            particlesContainer.id = 'particles-container';
            particlesContainer.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
                pointer-events: none;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            `;
            document.body.insertBefore(particlesContainer, document.body.firstChild);
        }
        
        // Three.js Scene Setup
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setClearColor(0x000000, 0);
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        particlesContainer.appendChild(renderer.domElement);
        
        // Health-themed particles
        const particles = [];
        const particleCount = 300;
        
        // Create enhanced DNA helix particles with glow effect
        for (let i = 0; i < particleCount; i++) {
            const geometry = new THREE.SphereGeometry(0.08, 12, 12);
            const material = new THREE.MeshBasicMaterial({ 
                color: new THREE.Color().setHSL(Math.random() * 0.3 + 0.5, 0.9, 0.7),
                transparent: true,
                opacity: 0.8
            });
            const particle = new THREE.Mesh(geometry, material);
            
            // Enhanced DNA helix pattern with double helix
            const angle = (i / particleCount) * Math.PI * 6;
            const radius = 3 + Math.sin(i * 0.1) * 0.5;
            const helixOffset = Math.sin(i * 0.5) * 0.3;
            
            particle.position.x = Math.cos(angle) * radius + helixOffset;
            particle.position.y = (i / particleCount) * 12 - 6;
            particle.position.z = Math.sin(angle) * radius + helixOffset;
            
            particle.userData = {
                originalY: particle.position.y,
                speed: Math.random() * 0.015 + 0.008,
                rotationSpeed: Math.random() * 0.03 + 0.02,
                pulseSpeed: Math.random() * 0.02 + 0.01,
                pulsePhase: Math.random() * Math.PI * 2
            };
            
            particles.push(particle);
            scene.add(particle);
        }
        
        // Create floating medical molecules with different shapes
        const moleculeCount = 80;
        for (let i = 0; i < moleculeCount; i++) {
            let geometry;
            const shapeType = Math.floor(Math.random() * 4);
            
            switch(shapeType) {
                case 0:
                    geometry = new THREE.SphereGeometry(0.06, 8, 8);
                    break;
                case 1:
                    geometry = new THREE.OctahedronGeometry(0.05);
                    break;
                case 2:
                    geometry = new THREE.TetrahedronGeometry(0.06);
                    break;
                case 3:
                    geometry = new THREE.BoxGeometry(0.08, 0.08, 0.08);
                    break;
            }
            
            const material = new THREE.MeshBasicMaterial({ 
                color: new THREE.Color().setHSL(Math.random() * 0.2 + 0.6, 0.9, 0.8),
                transparent: true,
                opacity: 0.9
            });
            const molecule = new THREE.Mesh(geometry, material);
            
            molecule.position.x = (Math.random() - 0.5) * 25;
            molecule.position.y = (Math.random() - 0.5) * 25;
            molecule.position.z = (Math.random() - 0.5) * 25;
            
            molecule.userData = {
                originalPosition: molecule.position.clone(),
                speed: Math.random() * 0.008 + 0.004,
                amplitude: Math.random() * 3 + 2,
                rotationSpeed: Math.random() * 0.02 + 0.01,
                type: shapeType
            };
            
            particles.push(molecule);
            scene.add(molecule);
        }
        
        // Create enhanced cell-like structures with nucleus
        const cellCount = 30;
        for (let i = 0; i < cellCount; i++) {
            // Outer cell membrane
            const cellGeometry = new THREE.SphereGeometry(0.4, 16, 16);
            const cellMaterial = new THREE.MeshBasicMaterial({ 
                color: new THREE.Color().setHSL(Math.random() * 0.1 + 0.4, 0.8, 0.9),
                transparent: true,
                opacity: 0.4,
                wireframe: true
            });
            const cell = new THREE.Mesh(cellGeometry, cellMaterial);
            
            // Inner nucleus
            const nucleusGeometry = new THREE.SphereGeometry(0.15, 12, 12);
            const nucleusMaterial = new THREE.MeshBasicMaterial({ 
                color: new THREE.Color().setHSL(Math.random() * 0.1 + 0.3, 0.9, 0.8),
                transparent: true,
                opacity: 0.8
            });
            const nucleus = new THREE.Mesh(nucleusGeometry, nucleusMaterial);
            cell.add(nucleus);
            
            cell.position.x = (Math.random() - 0.5) * 18;
            cell.position.y = (Math.random() - 0.5) * 18;
            cell.position.z = (Math.random() - 0.5) * 18;
            
            cell.userData = {
                originalPosition: cell.position.clone(),
                speed: Math.random() * 0.006 + 0.003,
                rotationSpeed: Math.random() * 0.015 + 0.008,
                nucleus: nucleus
            };
            
            particles.push(cell);
            scene.add(cell);
        }
        
        // Create floating energy particles
        const energyCount = 60;
        for (let i = 0; i < energyCount; i++) {
            const geometry = new THREE.SphereGeometry(0.03, 6, 6);
            const material = new THREE.MeshBasicMaterial({ 
                color: new THREE.Color().setHSL(Math.random() * 0.1 + 0.7, 1, 0.9),
                transparent: true,
                opacity: 0.9
            });
            const energy = new THREE.Mesh(geometry, material);
            
            energy.position.x = (Math.random() - 0.5) * 30;
            energy.position.y = (Math.random() - 0.5) * 30;
            energy.position.z = (Math.random() - 0.5) * 30;
            
            energy.userData = {
                originalPosition: energy.position.clone(),
                speed: Math.random() * 0.01 + 0.005,
                amplitude: Math.random() * 4 + 2,
                pulseSpeed: Math.random() * 0.03 + 0.02
            };
            
            particles.push(energy);
            scene.add(energy);
        }
        
        camera.position.z = 18;
        
        // Animation loop with enhanced effects
        function animate() {
            requestAnimationFrame(animate);
            const time = Date.now() * 0.001;
            
            // Animate enhanced DNA helix particles
            for (let i = 0; i < particleCount; i++) {
                const particle = particles[i];
                particle.position.y += particle.userData.speed;
                particle.rotation.y += particle.userData.rotationSpeed;
                
                // Pulsing effect
                const pulse = Math.sin(time * particle.userData.pulseSpeed + particle.userData.pulsePhase) * 0.1 + 1;
                particle.scale.setScalar(pulse);
                
                // Color variation
                const hue = (0.5 + Math.sin(time * 0.5 + i * 0.01) * 0.1) % 1;
                particle.material.color.setHSL(hue, 0.9, 0.7);
                
                if (particle.position.y > 6) {
                    particle.position.y = -6;
                }
            }
            
            // Animate enhanced medical molecules
            for (let i = particleCount; i < particleCount + moleculeCount; i++) {
                const molecule = particles[i];
                const moleculeTime = time * molecule.userData.speed;
                
                molecule.position.x = molecule.userData.originalPosition.x + Math.sin(moleculeTime) * molecule.userData.amplitude;
                molecule.position.y = molecule.userData.originalPosition.y + Math.cos(moleculeTime * 0.7) * molecule.userData.amplitude;
                molecule.position.z = molecule.userData.originalPosition.z + Math.sin(moleculeTime * 0.5) * molecule.userData.amplitude;
                
                molecule.rotation.x += molecule.userData.rotationSpeed;
                molecule.rotation.y += molecule.userData.rotationSpeed;
                molecule.rotation.z += molecule.userData.rotationSpeed;
                
                // Size variation based on type
                const sizePulse = Math.sin(moleculeTime * 2) * 0.1 + 1;
                molecule.scale.setScalar(sizePulse);
            }
            
            // Animate enhanced cells
            for (let i = particleCount + moleculeCount; i < particleCount + moleculeCount + cellCount; i++) {
                const cell = particles[i];
                const cellTime = time * cell.userData.speed;
                
                cell.position.x = cell.userData.originalPosition.x + Math.sin(cellTime) * 3;
                cell.position.y = cell.userData.originalPosition.y + Math.cos(cellTime * 0.8) * 3;
                cell.position.z = cell.userData.originalPosition.z + Math.sin(cellTime * 0.6) * 3;
                
                cell.rotation.x += cell.userData.rotationSpeed;
                cell.rotation.y += cell.userData.rotationSpeed;
                cell.rotation.z += cell.userData.rotationSpeed;
                
                // Nucleus rotation
                if (cell.userData.nucleus) {
                    cell.userData.nucleus.rotation.x += cell.userData.rotationSpeed * 1.5;
                    cell.userData.nucleus.rotation.y += cell.userData.rotationSpeed * 1.5;
                }
                
                // Breathing effect
                const breath = Math.sin(cellTime * 3) * 0.1 + 1;
                cell.scale.setScalar(breath);
            }
            
            // Animate energy particles
            for (let i = particleCount + moleculeCount + cellCount; i < particles.length; i++) {
                const energy = particles[i];
                const energyTime = time * energy.userData.speed;
                
                energy.position.x = energy.userData.originalPosition.x + Math.sin(energyTime) * energy.userData.amplitude;
                energy.position.y = energy.userData.originalPosition.y + Math.cos(energyTime * 0.8) * energy.userData.amplitude;
                energy.position.z = energy.userData.originalPosition.z + Math.sin(energyTime * 0.6) * energy.userData.amplitude;
                
                // Intense pulsing
                const energyPulse = Math.sin(energyTime * energy.userData.pulseSpeed) * 0.3 + 1;
                energy.scale.setScalar(energyPulse);
                
                // Brightness variation
                energy.material.opacity = Math.sin(energyTime * 2) * 0.3 + 0.7;
            }
            
            renderer.render(scene, camera);
        }
        
        // Handle window resize
        function handleResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        }
        
        window.addEventListener('resize', handleResize);
        
        // Start animation
        animate();
        
        // Cleanup function for page unload
        window.addEventListener('beforeunload', function() {
            window.removeEventListener('resize', handleResize);
            if (renderer && renderer.domElement && renderer.domElement.parentNode) {
                renderer.domElement.parentNode.removeChild(renderer.domElement);
            }
        });
    }
})(); 