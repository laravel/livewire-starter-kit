<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Documents - IESD File Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        .category-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        .category-card:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .category-card.active {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            border-left-color: #6366f1;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
        }
        .modal-backdrop {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
        
        /* Enhanced dropdown styles */
        .dropdown-menu {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15), 0 4px 10px rgba(0, 0, 0, 0.05);
            animation: dropdownSlide 0.2s ease-out;
            z-index: 9999 !important;
        }
        
        @keyframes dropdownSlide {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        /* Enhanced toast/alert styles */
        .toast-notification {
            animation: fadeIn 0.3s ease-out;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Confirmation dialog styles */
        .confirm-dialog {
            animation: modalFadeIn 0.3s ease-out;
        }
        
        /* Ensure dropdowns always appear on top */
        .relative .absolute {
            z-index: 9999 !important;
        }
        
        /* Header enhancements */
        header {
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        header .logo-container {
            transition: transform 0.3s ease;
        }
        
        header .logo-container:hover {
            transform: scale(1.05);
        }
        
        /* Dark Mode Styles */
        .dark {
            background-color: #1a1a1a;
        }
        
        .dark .glass-effect {
            background: rgba(40, 40, 40, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .dark .sidebar-gradient {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
        }
        
        .dark header {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            border-bottom-color: #1a202c;
        }
        
        .dark .category-card {
            border-left-color: transparent;
        }
        
        .dark .category-card:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .dark .category-card.active {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.2) 0%, rgba(139, 92, 246, 0.2) 100%);
            border-left-color: #6366f1;
        }
        
        .dark .category-card h4,
        .dark .category-card p {
            color: #e2e8f0;
        }
        
        .dark .text-gray-800 {
            color: #e2e8f0 !important;
        }
        
        .dark .text-gray-700 {
            color: #cbd5e0 !important;
        }
        
        .dark .text-gray-600 {
            color: #a0aec0 !important;
        }
        
        .dark .text-gray-500 {
            color: #718096 !important;
        }
        
        .dark .text-gray-400 {
            color: #4a5568 !important;
        }
        
        .dark .border-gray-200 {
            border-color: #2d3748 !important;
        }
        
        .dark .border-gray-300 {
            border-color: #4a5568 !important;
        }
        
        .dark .bg-gray-50 {
            background-color: #1a1a1a !important;
        }
        
        .dark .bg-white {
            background-color: #4f46e5 !important; /* Same as add document button */
        }
        
        .dark .bg-white.text-blue-600 {
            color: white !important;
        }
        
        .dark .hover\:bg-gray-100:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }
        
        .dark .hover\:bg-blue-50:hover {
            background-color: rgba(66, 153, 225, 0.1) !important;
        }
        
        .dark input,
        .dark textarea,
        .dark select {
            background-color: #2d3748 !important;
            color: #e2e8f0 !important;
            border-color: #4a5568 !important;
        }
        
        .dark input:focus,
        .dark textarea:focus,
        .dark select:focus {
            border-color: #6366f1 !important;
        }
        
        .dark table {
            background-color: #2d3748 !important;
        }
        
        .dark th {
            background-color: #1a202c !important;
            color: #e2e8f0 !important;
        }
        
        .dark td {
            color: #e2e8f0 !important;
        }
        
        .dark .border-b {
            border-color: #4a5568 !important;
        }
        
        .dark .dropdown-menu {
            background: rgba(45, 55, 72, 0.98) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        
        .dark .modal-backdrop {
            background: rgba(0, 0, 0, 0.7);
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>
<body class="bg-gray-50" x-data="fileTracker()">
    <!-- Header -->
    <header class="sidebar-gradient shadow-lg border-b-4 border-blue-700">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <!-- Left section: Logos and Title -->
                <div class="flex items-center space-x-4">
                    <!-- Logo 1 - Philippine Statistics Authority -->
                    <div class="w-12 h-12 bg-white rounded-full shadow-md flex items-center justify-center p-1 logo-container">
                        <img src="{{ asset('images/Philippine-Statistics-Authority-PSA-logo.png') }}" 
                             alt="PSA Logo" 
                             class="h-full w-full object-contain">
                    </div>
                    
                    <!-- Logo 2 -->
                    <div class="w-12 h-12 bg-white rounded-full shadow-md flex items-center justify-center p-2">
                        <img src="{{ asset('images/BP_color-logo-ver1-600px.png') }}" 
                             alt="BP Logo" 
                             class="h-8 w-8 object-contain">
                    </div>
                    
                    <!-- Title -->
                    <h1 class="text-white text-3xl font-bold tracking-wider ml-2">IESD FILE TRACKER</h1>
                </div>

                <!-- Right section: Theme Toggle and Logout -->
                <div class="flex items-center space-x-4">
                    <!-- Dark Mode Toggle -->
                    <button @click="toggleDarkMode()" 
                            class="text-white hover:text-blue-200 transition-colors p-2 rounded-lg hover:bg-blue-700"
                            title="Toggle Dark Mode">
                        <i :class="document.body.classList.contains('dark') ? 'fas fa-sun' : 'fas fa-moon'" class="text-xl"></i>
                    </button>

                    <!-- Logout Button -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="bg-white text-blue-600 px-6 py-2.5 rounded-lg shadow-md hover:bg-blue-50 transition-all duration-200 font-semibold flex items-center space-x-2 hover:shadow-lg">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="flex h-screen" style="height: calc(100vh - 80px);">
        <!-- Enhanced Sidebar -->
        <aside class="w-80 sidebar-gradient shadow-2xl relative z-40" style="overflow: visible;">
            <div class="p-6 text-white">
                <!-- Header -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-2 flex items-center">
                        <i class="fas fa-folder-tree mr-3"></i>
                        Categories
                    </h2>
                    <p class="text-white/80 text-sm">Manage your document categories</p>
                </div>
                
                <!-- Add Category Section -->
                <div class="mb-6">
                    <div class="glass-effect rounded-lg p-4">
                        <h3 class="text-gray-800 font-semibold mb-3 flex items-center">
                            <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>
                            Add New Category
                        </h3>
                        <div class="space-y-3">
                            <input type="text" 
                                   x-model="newCategory.category_name" 
                                   placeholder="Category name" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-800">
                            <textarea x-show="newCategory.category_name.trim() !== ''" 
                                      x-transition:enter="transition ease-out duration-300"
                                      x-transition:enter-start="opacity-0 transform -translate-y-2"
                                      x-transition:enter-end="opacity-100 transform translate-y-0"
                                      x-transition:leave="transition ease-in duration-200"
                                      x-transition:leave-start="opacity-100 transform translate-y-0"
                                      x-transition:leave-end="opacity-0 transform -translate-y-2"
                                      x-model="newCategory.description" 
                                      placeholder="Description (optional)" 
                                      rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-800 resize-none"></textarea>
                            
                            <button @click="addCategory" 
                                    class="w-full btn-primary text-white py-2 rounded-lg font-semibold">
                                <i class="fas fa-plus mr-2"></i>Add Category
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Category List -->
                <div class="space-y-2" style="overflow: visible;">
                    <!-- All Categories Option (Moved to Top) -->
                    <div class="category-card glass-effect rounded-lg p-4 cursor-pointer"
                         :class="{'active': selectedCategory === null}"
                         @click="selectCategory(null)">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 rounded-full bg-gradient-to-r from-purple-500 to-pink-500"></div>
                                <div>
                                    <h4 class="font-semibold text-gray-800">All Categories</h4>
                                    <p class="text-xs text-gray-600">View all documents</p>
                                </div>
                            </div>
                            <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full font-semibold"
                                  x-text="`${totalDocuments} docs`"></span>
                        </div>
                    </div>
                    
                    <template x-for="category in categories" :key="category.id">
                        <div class="category-card glass-effect rounded-lg p-4 cursor-pointer relative"
                             :class="{'active': selectedCategory === category.id}"
                             @click="selectCategory(category.id)"
                             style="overflow: visible;">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 rounded-full"
                                         :style="`background-color: ${category.color}`"></div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800" x-text="category.category_name"></h4>
                                        <p class="text-xs text-gray-600" x-text="category.description || 'No description'"></p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="relative group">
                                        <button @click.stop="toggleDropdown(category.id)" 
                                                class="text-gray-400 hover:text-gray-600 p-2 hover:bg-gray-100 rounded-lg transition-all duration-200">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div x-show="activeDropdown === category.id" 
                                             @click.away="activeDropdown = null"
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 scale-95 transform -translate-x-2"
                                             x-transition:enter-end="opacity-100 scale-100 transform translate-x-0"
                                             x-transition:leave="transition ease-in duration-150"
                                             x-transition:leave-start="opacity-100 scale-100 transform translate-x-0"
                                             x-transition:leave-end="opacity-0 scale-95 transform -translate-x-2"
                                             class="absolute left-full top-0 ml-1 w-52 dropdown-menu rounded-lg shadow-xl border border-gray-200"
                                             style="z-index: 9999; position: absolute;">
                                            <button @click.stop="editCategory(category)" 
                                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center transition-all duration-200 group">
                                                <i class="fas fa-edit mr-3 text-blue-500 group-hover:scale-110 transition-transform"></i>
                                                <span class="font-medium">Edit Category</span>
                                            </button>
                                            <div class="border-t border-gray-100"></div>
                                            <button @click.stop="confirmDeleteCategory(category)" 
                                                    class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 flex items-center transition-all duration-200 group">
                                                <i class="fas fa-trash mr-3 group-hover:scale-110 transition-transform"></i>
                                                <span class="font-medium">Delete Category</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Admin Files Button -->
                <div class="mt-6" x-show="hasAdminDocuments">
                    <button @click="selectCategory('admin')" 
                            class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white py-3 rounded-lg font-semibold hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-shield-alt mr-2"></i>
                        ADMIN FILES
                    </button>
                </div>
                
                            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <div class="glass-effect rounded-xl shadow-xl">
                <!-- Header with Search and Add Button -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 max-w-lg">
                            <div class="relative">
                                <input type="text" 
                                       x-model="searchTerm" 
                                       placeholder="Search documents by name or category..." 
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
                            </div>
                        </div>
                        <button @click="showAddDocument = true" 
                                class="ml-4 btn-primary text-white px-6 py-3 rounded-lg font-semibold">
                            <i class="fas fa-plus mr-2"></i>Add Document
                        </button>
                    </div>
                </div>

                <!-- Documents Table -->
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-file-alt mr-3 text-indigo-600"></i>
                            Documents
                        </h2>
                        <div class="text-sm text-gray-600">
                            Showing <span x-text="filteredDocuments.length" class="font-semibold"></span> of <span x-text="documents.length" class="font-semibold"></span> documents
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50">
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">
                                        <input type="checkbox" @change="toggleSelectAll" class="mr-2">
                                        Name
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Category</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Year</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Link</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="document in filteredDocuments" :key="document.id">
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-4 px-4">
                                            <div class="flex items-center">
                                                <input type="checkbox" :value="document.id" x-model="selectedDocuments" class="mr-3 w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500">
                                                <div>
                                                    <div class="font-semibold text-gray-800" x-text="document.document_name"></div>
                                                    <div class="text-xs text-gray-500" x-text="formatDate(document.created_at)"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                                                  :style="`background-color: ${getCategoryColor(document.category_name)}20; color: ${getCategoryColor(document.category_name)}`"
                                                  x-text="document.category_name"></span>
                                        </td>
                                        <td class="py-4 px-4 text-gray-600">2024</td>
                                        <td class="py-4 px-4">
                                            <a :href="document.url" 
                                               target="_blank" 
                                               class="text-indigo-600 hover:text-indigo-800 underline flex items-center">
                                                <i class="fas fa-external-link-alt mr-1 text-xs"></i>
                                                <span class="truncate max-w-xs" x-text="document.url"></span>
                                            </a>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex space-x-2">
                                                <button @click="editDocument(document)" 
                                                        class="text-blue-600 hover:text-blue-800 p-2 hover:bg-blue-50 rounded-lg transition-colors"
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button @click="deleteDocument(document.id)" 
                                                        class="text-red-600 hover:text-red-800 p-2 hover:bg-red-50 rounded-lg transition-colors"
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        
                        <div x-show="filteredDocuments.length === 0" class="text-center py-12">
                            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 text-lg">No documents found</p>
                            <p class="text-gray-400 text-sm mt-2">Try adjusting your search or add a new document</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Document Modal -->
    <div x-show="showAddDocument" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 transform -translate-y-4 scale-95"
         class="fixed inset-0 modal-backdrop flex items-center justify-center z-50" x-cloak>
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-file-plus mr-3 text-indigo-600"></i>
                Add New Document
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select x-model="newDocument.category_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select a category</option>
                        <template x-for="category in categories" :key="category.id">
                            <option :value="category.id" x-text="category.category_name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Document Name</label>
                    <input type="text" 
                           x-model="newDocument.document_name" 
                           placeholder="Enter document name" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                    <input type="url" 
                           x-model="newDocument.url" 
                           placeholder="https://example.com/document" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button @click="showAddDocument = false; resetNewDocument()" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button @click="addDocument" 
                        class="px-4 py-2 btn-primary text-white rounded-lg">
                    <i class="fas fa-plus mr-2"></i>Add Document
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div x-show="showEditCategory" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 transform -translate-y-4 scale-95"
         class="fixed inset-0 modal-backdrop flex items-center justify-center z-50" x-cloak>
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-edit mr-3 text-indigo-600"></i>
                Edit Category
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category Name</label>
                    <input type="text" 
                           x-model="editingCategory.category_name" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea x-model="editingCategory.description" 
                              rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                </div>
                <div class="flex space-x-2">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                        <input type="color" 
                               x-model="editingCategory.color" 
                               class="w-full h-10 border border-gray-300 rounded cursor-pointer">
                    </div>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button @click="showEditCategory = false" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button @click="updateCategory" 
                        class="px-4 py-2 btn-primary text-white rounded-lg">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteConfirm" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 transform -translate-y-4 scale-95"
         class="fixed inset-0 modal-backdrop flex items-center justify-center z-50" x-cloak>
        <div class="confirm-dialog bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Category</h3>
                <p class="text-sm text-gray-600 mb-6">
                    Are you sure you want to delete this category? 
                    <span class="font-semibold text-gray-900" x-text="categoryToDelete?.category_name"></span>
                    <br>
                    This will also delete all documents in this category.
                </p>
                <div class="flex justify-center space-x-3">
                    <button @click="cancelDelete()" 
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                        Cancel
                    </button>
                    <button @click="deleteCategoryConfirmed()" 
                            class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Document Modal -->
    <div x-show="showEditDocument" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 transform -translate-y-4 scale-95"
         class="fixed inset-0 modal-backdrop flex items-center justify-center z-50" x-cloak>
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-edit mr-3 text-indigo-600"></i>
                Edit Document
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select x-model="editingDocument.category_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select a category</option>
                        <template x-for="category in categories" :key="category.id">
                            <option :value="category.id" x-text="category.category_name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Document Name</label>
                    <input type="text" 
                           x-model="editingDocument.document_name" 
                           placeholder="Enter document name" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                    <input type="url" 
                           x-model="editingDocument.url" 
                           placeholder="https://example.com/document" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button @click="showEditDocument = false" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button @click="updateDocument" 
                        class="px-4 py-2 btn-primary text-white rounded-lg">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Document Confirmation Modal -->
    <div x-show="showDeleteDocumentConfirm" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 transform -translate-y-4 scale-95"
         class="fixed inset-0 modal-backdrop flex items-center justify-center z-50" x-cloak>
        <div class="confirm-dialog bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Document</h3>
                <p class="text-sm text-gray-600 mb-6">
                    Are you sure you want to delete this document? 
                    <span class="font-semibold text-gray-900" x-text="documentToDelete?.document_name"></span>
                    <br>
                    This action cannot be undone.
                </p>
                <div class="flex justify-center space-x-3">
                    <button @click="cancelDeleteDocument()" 
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                        Cancel
                    </button>
                    <button @click="deleteDocumentConfirmed()" 
                            class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fileTracker() {
            return {
                categories: @json($categories),
                categoriesWithDocuments: @json($categoriesWithDocuments),
                uncategorizedDocuments: @json($uncategorizedDocuments),
                documents: @json($documents),
                selectedCategory: null,
                searchTerm: '',
                showAddDocument: false,
                showEditDocument: false,
                showEditCategory: false,
                showDeleteConfirm: false,
                showDeleteDocumentConfirm: false,
                activeDropdown: null,
                categoryToDelete: null,
                documentToDelete: null,
                newCategory: {
                    category_name: '',
                    description: '',
                    color: '#6B46C1',
                    icon: 'folder'
                },
                editingCategory: {
                    id: null,
                    category_name: '',
                    description: '',
                    color: '#6B46C1',
                    icon: 'folder'
                },
                newDocument: {
                    category_id: '',
                    document_name: '',
                    url: ''
                },
                editingDocument: {
                    id: null,
                    category_id: '',
                    document_name: '',
                    url: ''
                },
                selectedDocuments: [],
                
                get filteredDocuments() {
                    let filtered = this.documents;
                    
                    if (this.selectedCategory === 'admin') {
                        // Show only admin documents
                        filtered = filtered.filter(doc => doc.is_admin === true);
                    } else if (this.selectedCategory !== null) {
                        // Filter by selected category ID using foreach logic
                        filtered = filtered.filter(doc => {
                            // Ensure proper category comparison
                            return doc.category_id === this.selectedCategory || 
                                   (doc.category && doc.category.id === this.selectedCategory);
                        });
                    }
                    
                    if (this.searchTerm) {
                        const search = this.searchTerm.toLowerCase();
                        filtered = filtered.filter(doc => 
                            doc.document_name.toLowerCase().includes(search) ||
                            (doc.category_name && doc.category_name.toLowerCase().includes(search)) ||
                            (doc.category && doc.category.category_name && doc.category.category_name.toLowerCase().includes(search))
                        );
                    }
                    
                    return filtered;
                },
                
                get totalDocuments() {
                    return this.documents.length;
                },
                
                get hasAdminDocuments() {
                    return this.documents.some(doc => doc.is_admin === true);
                },
                
                selectCategory(categoryId) {
                    this.selectedCategory = categoryId;
                },
                
                get isAdminFilesSelected() {
                    return this.selectedCategory === 'admin';
                },
                
                async addCategory() {
                    if (!this.newCategory.category_name.trim()) {
                        this.showToast('Please enter a category name', 'error');
                        return;
                    }
                    
                    try {
                        const response = await fetch('/api/categories', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(this.newCategory)
                        });
                        
                        if (response.ok) {
                            const newCategory = await response.json();
                            this.categories.push(newCategory);
                            this.resetNewCategory();
                            this.showToast('Category added successfully', 'success');
                        }
                    } catch (error) {
                        console.error('Error adding category:', error);
                        this.showToast('Error adding category', 'error');
                    }
                },
                
                editCategory(category) {
                    this.editingCategory = { ...category };
                    this.showEditCategory = true;
                },
                
                async updateCategory() {
                    if (!this.editingCategory.category_name.trim()) {
                        this.showToast('Please enter a category name', 'error');
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/api/categories/${this.editingCategory.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(this.editingCategory)
                        });
                        
                        if (!response.ok) {
                            const errorData = await response.json();
                            console.error('Update error:', errorData);
                            this.showToast(`Error: ${errorData.message || 'Failed to update category'}`, 'error');
                            return;
                        }
                        
                        const updatedCategory = await response.json();
                        const index = this.categories.findIndex(c => c.id === updatedCategory.id);
                        if (index !== -1) {
                            this.categories[index] = updatedCategory;
                        }
                        this.showEditCategory = false;
                        this.showToast('Category updated successfully', 'success');
                    } catch (error) {
                        console.error('Error updating category:', error);
                        this.showToast('Error updating category', 'error');
                    }
                },
                
                toggleDropdown(categoryId) {
                    this.activeDropdown = this.activeDropdown === categoryId ? null : categoryId;
                },
                
                confirmDeleteCategory(category) {
                    this.categoryToDelete = category;
                    this.showDeleteConfirm = true;
                    this.activeDropdown = null;
                },
                
                async deleteCategoryConfirmed() {
                    if (!this.categoryToDelete) return;
                    
                    try {
                        const response = await fetch(`/api/categories/${this.categoryToDelete.id}/force-delete`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        
                        if (!response.ok) {
                            const errorData = await response.json();
                            console.error('Delete error:', errorData);
                            this.showToast(`Error: ${errorData.message || 'Failed to delete category'}`, 'error');
                            return;
                        }
                        
                        this.categories = this.categories.filter(c => c.id !== this.categoryToDelete.id);
                        this.documents = this.documents.filter(doc => doc.category_id !== this.categoryToDelete.id);
                        if (this.selectedCategory === this.categoryToDelete.id) {
                            this.selectedCategory = null;
                        }
                        this.showDeleteConfirm = false;
                        this.categoryToDelete = null;
                        this.showToast('Category deleted successfully', 'success');
                    } catch (error) {
                        console.error('Error deleting category:', error);
                        this.showToast('Error deleting category', 'error');
                    }
                },
                
                cancelDelete() {
                    this.showDeleteConfirm = false;
                    this.categoryToDelete = null;
                },
                
                async deleteCategory(categoryId) {
                    if (!confirm('Are you sure you want to delete this category? This will also delete all documents in this category.')) {
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/api/categories/${categoryId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        
                        if (!response.ok) {
                            const errorData = await response.json();
                            console.error('Delete error:', errorData);
                            this.showToast(`Error: ${errorData.message || 'Failed to delete category'}`, 'error');
                            return;
                        }
                        
                        this.categories = this.categories.filter(c => c.id !== categoryId);
                        this.documents = this.documents.filter(doc => doc.category_id !== categoryId);
                        if (this.selectedCategory === categoryId) {
                            this.selectedCategory = null;
                        }
                        this.showToast('Category deleted successfully', 'success');
                    } catch (error) {
                        console.error('Error deleting category:', error);
                        this.showToast('Error deleting category', 'error');
                    }
                },
                
                async addDocument() {
                    if (!this.newDocument.document_name.trim() || !this.newDocument.url.trim() || !this.newDocument.category_id) {
                        this.showToast('Please fill in all required fields', 'error');
                        return;
                    }
                    
                    const category = this.categories.find(c => c.id == this.newDocument.category_id);
                    
                    try {
                        const response = await fetch('/api/documents', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                ...this.newDocument,
                                category_name: category.category_name
                            })
                        });
                        
                        if (!response.ok) {
                            const errorData = await response.json();
                            console.error('Error adding document:', errorData);
                            this.showToast(`Error: ${errorData.message || 'Failed to add document'}`, 'error');
                            return;
                        }
                        
                        const newDocument = await response.json();
                        this.documents.push({
                            ...newDocument,
                            category_name: category.category_name
                        });
                        this.resetNewDocument();
                        this.showAddDocument = false;
                        this.showToast('Document added successfully', 'success');
                    } catch (error) {
                        console.error('Error adding document:', error);
                        this.showToast('Error adding document', 'error');
                    }
                },
                
                editDocument(document) {
                    this.editingDocument = { 
                        id: document.id,
                        category_id: document.category_id || (document.category ? document.category.id : ''),
                        document_name: document.document_name,
                        url: document.url
                    };
                    this.showEditDocument = true;
                },
                
                async updateDocument() {
                    if (!this.editingDocument.document_name.trim() || !this.editingDocument.url.trim() || !this.editingDocument.category_id) {
                        this.showToast('Please fill in all required fields', 'error');
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/api/documents/${this.editingDocument.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(this.editingDocument)
                        });
                        
                        if (!response.ok) {
                            const errorData = await response.json();
                            console.error('Error updating document:', errorData);
                            this.showToast(`Error: ${errorData.message || 'Failed to update document'}`, 'error');
                            return;
                        }
                        
                        const updatedDocument = await response.json();
                        const index = this.documents.findIndex(doc => doc.id === updatedDocument.id);
                        if (index !== -1) {
                            this.documents[index] = updatedDocument;
                        }
                        this.showEditDocument = false;
                        this.showToast('Document updated successfully', 'success');
                    } catch (error) {
                        console.error('Error updating document:', error);
                        this.showToast('Error updating document', 'error');
                    }
                },
                
                async deleteDocument(documentId) {
                    const document = this.documents.find(doc => doc.id === documentId);
                    if (!document) return;
                    
                    this.documentToDelete = document;
                    this.showDeleteDocumentConfirm = true;
                },
                
                async deleteDocumentConfirmed() {
                    if (!this.documentToDelete) return;
                    
                    try {
                        const response = await fetch(`/api/documents/${this.documentToDelete.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        
                        if (!response.ok) {
                            const errorData = await response.json();
                            console.error('Error deleting document:', errorData);
                            this.showToast(`Error: ${errorData.message || 'Failed to delete document'}`, 'error');
                            return;
                        }
                        
                        this.documents = this.documents.filter(doc => doc.id !== this.documentToDelete.id);
                        this.showDeleteDocumentConfirm = false;
                        this.documentToDelete = null;
                        this.showToast('Document deleted successfully', 'success');
                    } catch (error) {
                        console.error('Error deleting document:', error);
                        this.showToast('Error deleting document', 'error');
                    }
                },
                
                cancelDeleteDocument() {
                    this.showDeleteDocumentConfirm = false;
                    this.documentToDelete = null;
                },
                
                toggleSelectAll(event) {
                    if (event.target.checked) {
                        this.selectedDocuments = this.filteredDocuments.map(doc => doc.id);
                    } else {
                        this.selectedDocuments = [];
                    }
                },
                
                resetNewDocument() {
                    this.newDocument = {
                        category_id: '',
                        document_name: '',
                        url: ''
                    };
                },
                
                resetNewCategory() {
                    this.newCategory = {
                        category_name: '',
                        description: '',
                        color: '#6B46C1',
                        icon: 'folder'
                    };
                },
                
                toggleDarkMode() {
                    document.body.classList.toggle('dark');
                    // No alert/notification - just toggle silently
                },
                
                getCategoryColor(categoryName) {
                    const category = this.categories.find(c => c.category_name === categoryName);
                    return category ? category.color : '#6B46C1';
                },
                
                formatDate(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    });
                },
                
                showToast(message, type = 'info') {
                    // Enhanced toast notification with animations
                    const toast = document.createElement('div');
                    const bgColor = type === 'success' ? 'bg-gradient-to-r from-green-500 to-emerald-600' : 
                                   type === 'error' ? 'bg-gradient-to-r from-red-500 to-rose-600' : 
                                   'bg-gradient-to-r from-blue-500 to-indigo-600';
                    
                    const icon = type === 'success' ? 'fa-check-circle' : 
                                type === 'error' ? 'fa-exclamation-circle' : 
                                'fa-info-circle';
                    
                    toast.className = `toast-notification fixed top-4 right-4 px-6 py-4 rounded-lg text-white z-50 ${bgColor} flex items-center space-x-3 min-w-[300px]`;
                    toast.style.transition = 'opacity 1s ease-out';
                    toast.innerHTML = `
                        <i class="fas ${icon} text-xl"></i>
                        <div class="flex-1">
                            <p class="font-semibold">${type.charAt(0).toUpperCase() + type.slice(1)}</p>
                            <p class="text-sm opacity-90">${message}</p>
                        </div>
                        <button onclick="this.parentElement.style.opacity='0'; setTimeout(() => this.parentElement.remove(), 1000)" class="ml-4 hover:opacity-75 transition-opacity">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    
                    document.body.appendChild(toast);
                    
                    // Auto-remove after 5 seconds
                    setTimeout(() => {
                        if (toast && toast.parentElement) {
                            toast.style.opacity = '0';
                            setTimeout(() => {
                                if (toast && toast.parentElement) {
                                    toast.parentElement.removeChild(toast);
                                }
                            }, 1000);
                        }
                    }, 5000);
                }
            }
        }
    </script>
</body>
</html>
