<div class="w-full bg-white shadow-lg rounded-lg p-8 border-l-4 border-red-500">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Възникна технически проблем</h1>

    <p class="text-gray-600 mb-6">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <?php if ($debugMode && isset($trace)): ?>
        <div class="bg-gray-800 text-gray-200 p-4 rounded text-sm overflow-auto mb-6">
            <h3 class="font-bold mb-2 text-red-400">Техническа информация:</h3>
            <p class="mb-2">Файл:
                <?php echo htmlspecialchars($file); ?> :
                <?php echo $line; ?>
            </p>
            <pre class="whitespace-pre-wrap"><?php echo htmlspecialchars($trace); ?></pre>
        </div>
    <?php endif; ?>

    <a href="/" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">Връщане към
        началото</a>
</div>
