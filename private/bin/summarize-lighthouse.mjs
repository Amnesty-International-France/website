import { createRequire } from 'node:module';
import fs from 'node:fs';
import path from 'node:path';

const require = createRequire(import.meta.url);
const config = require('../lighthouserc.cjs');

const lighthouseDir = path.resolve(process.cwd(), '.lighthouseci');
const outputPath = path.resolve(process.cwd(), '.lighthouseci/lhci-output.log');
const summaryPath = process.env.GITHUB_STEP_SUMMARY;
const auditUrls = config.ci?.collect?.url ?? [];
const scoreCategories = [
  ['performance', 'Performance'],
  ['accessibility', 'Accessibility'],
  ['seo', 'SEO'],
];

const output = fs.existsSync(outputPath) ? fs.readFileSync(outputPath, 'utf8') : '';
const reportLinks = new Map(
  [...output.matchAll(/Uploading median LHR of (https?:\/\/\S+)\.\.\.success!\nOpen the report at (https?:\/\/\S+)/g)].map((match) => [
    match[1],
    match[2],
  ]),
);
const lhrs = fs.existsSync(lighthouseDir)
  ? fs
      .readdirSync(lighthouseDir)
      .filter((file) => /^lhr-\d+\.json$/.test(file))
      .map((file) => JSON.parse(fs.readFileSync(path.join(lighthouseDir, file), 'utf8')))
  : [];
const lhrsByUrl = lhrs.reduce((grouped, lhr) => {
  const url = lhr.finalUrl ?? lhr.requestedUrl;

  if (!url) {
    return grouped;
  }

  grouped.set(url, [...(grouped.get(url) ?? []), lhr]);

  return grouped;
}, new Map());

function formatScore(score) {
  return typeof score === 'number' ? String(Math.round(score * 100)) : 'n/a';
}

function median(values) {
  const sortedValues = values.filter((value) => typeof value === 'number').sort((a, b) => a - b);

  if (sortedValues.length === 0) {
    return null;
  }

  return sortedValues[Math.floor(sortedValues.length / 2)];
}

const lines = [
  '## Lighthouse summary',
  '',
  'Lighthouse is informational for this workflow. Warnings do not block the PR.',
  '',
];
const logLines = [
  'Lighthouse summary',
  '',
  'Lighthouse is informational for this workflow. Warnings do not block the PR.',
  '',
];

if (lhrsByUrl.size > 0) {
  const headers = ['Page', ...scoreCategories.map(([, label]) => label), 'Report'];
  const alignment = ['---', ...scoreCategories.map(() => '---:'), '---'];
  const urls = auditUrls.length > 0 ? auditUrls : [...lhrsByUrl.keys()];

  lines.push(`| ${headers.join(' | ')} |`, `| ${alignment.join(' | ')} |`);

  urls.forEach((url) => {
    const reports = lhrsByUrl.get(url) ?? [];
    const scores = scoreCategories.map(([key]) =>
      formatScore(median(reports.map((report) => report.categories?.[key]?.score))),
    );
    const reportLink = reportLinks.get(url) ? `[Open report](${reportLinks.get(url)})` : 'See artifact';
    const rawReportLink = reportLinks.get(url) ?? 'See artifact';

    lines.push(`| ${[url, ...scores, reportLink].join(' | ')} |`);
    logLines.push(
      `Page: ${url}`,
      `Scores: Performance ${scores[0]}/100, Accessibility ${scores[1]}/100, SEO ${scores[2]}/100`,
      `Report: ${rawReportLink}`,
      '',
    );
  });
} else {
  lines.push('No Lighthouse scores were found. Check the workflow logs and lighthouse-reports artifact.');
  logLines.push('No Lighthouse scores were found. Check the workflow logs and lighthouse-reports artifact.');
}

lines.push('');

const summary = `${lines.join('\n')}\n`;
const logSummary = `${logLines.join('\n')}\n`;

if (summaryPath) {
  fs.appendFileSync(summaryPath, summary);
}

process.stdout.write(logSummary);
