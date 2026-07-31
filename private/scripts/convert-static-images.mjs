import fs from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import sharp from 'sharp';

const dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(dirname, '..');
const staticImagesDir = path.join(root, 'src', 'static', 'images');
const sourceExtensions = new Set(['.jpg', '.jpeg', '.png']);
const formats = [
  { extension: '.avif', options: { quality: 55 } },
  { extension: '.webp', options: { quality: 82 } },
];

async function exists(file) {
  try {
    await fs.access(file);
    return true;
  } catch {
    return false;
  }
}

async function walk(dir) {
  const entries = await fs.readdir(dir, { withFileTypes: true });
  const files = [];

  for (const entry of entries) {
    const fullPath = path.join(dir, entry.name);

    if (entry.isDirectory()) {
      files.push(...await walk(fullPath));
      continue;
    }

    if (sourceExtensions.has(path.extname(entry.name).toLowerCase())) {
      files.push(fullPath);
    }
  }

  return files;
}

async function convert(file) {
  const source = await fs.stat(file);
  const parsed = path.parse(file);
  const image = sharp(file, { animated: false }).rotate();
  const results = [];

  for (const format of formats) {
    const output = path.join(parsed.dir, `${parsed.name}${format.extension}`);

    if (await exists(output)) {
      const current = await fs.stat(output);
      if (current.mtimeMs >= source.mtimeMs && current.size < source.size) {
        results.push({ output, status: 'kept' });
        continue;
      }
    }

    if (format.extension === '.avif') {
      await image.clone().avif(format.options).toFile(output);
    } else {
      await image.clone().webp(format.options).toFile(output);
    }

    const generated = await fs.stat(output);
    if (generated.size >= source.size) {
      await fs.unlink(output);
      results.push({ output, status: 'skipped-larger' });
      continue;
    }

    results.push({ output, status: 'generated' });
  }

  return results;
}

const files = await walk(staticImagesDir);
let generated = 0;
let kept = 0;
let skipped = 0;

for (const file of files) {
  const results = await convert(file);

  for (const result of results) {
    if (result.status === 'generated') {
      generated += 1;
    } else if (result.status === 'kept') {
      kept += 1;
    } else {
      skipped += 1;
    }
  }
}

console.log(`Static image conversion complete: ${generated} generated, ${kept} kept, ${skipped} skipped.`);
