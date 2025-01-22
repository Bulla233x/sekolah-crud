<?php
session_start();
require_once 'konek.php';

$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM siswa WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Add logout handling
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'create':
                $stmt = $pdo->prepare("INSERT INTO siswa (nama, tanggal_lahir, alamat, nomor_hp) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_POST['nama'], $_POST['tanggal_lahir'], $_POST['alamat'], $_POST['nomor_hp']]);
                break;
                
            case 'update':
                $stmt = $pdo->prepare("UPDATE siswa SET nama=?, tanggal_lahir=?, alamat=?, nomor_hp=? WHERE id=?");
                $stmt->execute([$_POST['nama'], $_POST['tanggal_lahir'], $_POST['alamat'], $_POST['nomor_hp'], $_POST['id']]);
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM siswa WHERE id=?");
                $stmt->execute([$_POST['id']]);
                break;
        }
        header('Location: index.php');
        exit();
    }
}

// Get student data with proper error handling
try {
    $stmt = $pdo->query("SELECT * FROM siswa ORDER BY created_at DESC");
    $siswaList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['error'] = "Error loading data: " . $e->getMessage();
    $siswaList = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏫</text></svg>">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Add logout button -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Data Siswa</h1>
            <form method="POST">
            <button type="submit" 
                name="logout" 
                onclick="return confirm('Mau Keluar Byee')"
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                Logout
            </button>
            </form>
        </div>
        
        <!-- Form Input -->
        <form method="POST" class="bg-white p-6 rounded-lg shadow-md mb-8">
            <input type="hidden" name="action" value="<?php echo isset($_GET['edit']) ? 'update' : 'create'; ?>">
            <?php if (isset($_GET['edit'])): ?>
                <input type="hidden" name="id" value="<?php echo $_GET['edit']; ?>">
            <?php endif; ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama</label>
                    <input type="text" 
                           name="nama" 
                           required 
                           value="<?php echo isset($editData) ? htmlspecialchars($editData['nama']) : ''; ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                    <input type="date" 
                           name="tanggal_lahir" 
                           required 
                           value="<?php echo isset($editData) ? htmlspecialchars($editData['tanggal_lahir']) : ''; ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Alamat</label>
                    <input type="text" 
                           name="alamat" 
                           required 
                           value="<?php echo isset($editData) ? htmlspecialchars($editData['alamat']) : ''; ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nomor HP</label>
                    <input type="tel" 
                           name="nomor_hp" 
                           required 
                           value="<?php echo isset($editData) ? htmlspecialchars($editData['nomor_hp']) : ''; ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>
            
            <div class="mt-4 flex justify-end space-x-3">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md">
                    <?php echo isset($_GET['edit']) ? 'Update' : 'Simpan'; ?>
                </button>
            </div>
        </form>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Lahir</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alamat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nomor HP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if(empty($siswaList)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Kosong</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($siswaList as $index => $siswa): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo $index + 1; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($siswa['nama']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($siswa['tanggal_lahir']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($siswa['alamat']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($siswa['nomor_hp']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="?edit=<?php echo htmlspecialchars($siswa['id']); ?>" class="text-green-600 hover:text-green-900">EDIT</a>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($siswa['id']); ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Mau Sliahkan Hapus Data ini?')">HAPUS</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>