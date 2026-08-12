<?php

declare(strict_types=1);

$escape = static fn (mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);
?>
<!doctype html>
<html lang="bg">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title><?= $escape($title ?? 'Административен вход') ?> · Flex CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <main class="flex min-h-screen items-center justify-center px-4 py-10">
        <section class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900 p-7 shadow-2xl shadow-black/30 sm:p-9">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 text-xl font-bold">F</div>
                <h1 class="text-2xl font-bold">Административен вход</h1>
                <p class="mt-2 text-sm leading-6 text-slate-400">Достъпът е разрешен само за оторизирани администратори на сайта.</p>
            </div>

            <?php if (is_string($error ?? null) && $error !== ''): ?>
                <div role="alert" class="mb-5 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                    <?= $escape($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/login" class="space-y-5">
                <input type="hidden" name="_token" value="<?= $escape($csrfToken ?? '') ?>">

                <label class="block text-sm font-semibold">
                    Имейл
                    <input type="email" name="email" value="<?= $escape($email ?? '') ?>" required autocomplete="username"
                           class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950 px-3.5 py-3 text-slate-100 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                </label>

                <label class="block text-sm font-semibold">
                    Парола
                    <input type="password" name="password" required autocomplete="current-password"
                           class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950 px-3.5 py-3 text-slate-100 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                </label>

                <label class="flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-600 bg-slate-950 text-indigo-600">
                    Запомни ме
                </label>

                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-3 font-bold text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    Вход
                </button>
            </form>
        </section>
    </main>
</body>
</html>
