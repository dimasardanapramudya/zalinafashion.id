path = "resources/views/layouts/admin.blade.php"

with open(path, "r", encoding="utf-8") as f:
    lines = f.readlines()

target_idx = None

for i, line in enumerate(lines):
    if "admin.orders.index" in line:
        target_idx = i
        break

if target_idx is None:
    print("GAGAL: baris 'admin.orders.index' tidak ditemukan.")
else:
    close_idx = None
    for j in range(target_idx, min(target_idx + 10, len(lines))):
        if lines[j].strip() == "],":
            close_idx = j
            break

    if close_idx is None:
        print("GAGAL: baris penutup ']' setelah admin.orders.index tidak ditemukan.")
    else:
        new_block = [
            "\n",
            "    [\n",
            "        'route' => 'admin.returns.index',\n",
            "        'label' => 'Retur',\n",
            "        'icon' => 'M3 10h10a4 4 0 010 8H7m-4-8l4-4m-4 4l4 4'\n",
            "    ],\n",
        ]

        lines = lines[:close_idx + 1] + new_block + lines[close_idx + 1:]

        with open(path, "w", encoding="utf-8") as f:
            f.writelines(lines)

        print("DONE: menu Retur ditambahkan ke sidebar (setelah baris " + str(close_idx + 1) + ")")
