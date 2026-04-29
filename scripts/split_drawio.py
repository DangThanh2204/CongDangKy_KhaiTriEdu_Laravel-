"""Split a multi-page .drawio file into per-page .drawio files."""
import re
import sys
from pathlib import Path

SRC = Path(__file__).resolve().parent.parent / "docs" / "uml" / "test.drawio"
OUT_DIR = SRC.parent / "pages"

content = SRC.read_text(encoding="utf-8")

mxfile_open = re.search(r"<mxfile\b[^>]*>", content)
if not mxfile_open:
    sys.exit("No <mxfile> opening tag found")
mxfile_tag = mxfile_open.group(0)

diagrams = re.findall(r"<diagram\b[^>]*?\sid=\"([^\"]+)\"[^>]*>[\s\S]*?</diagram>", content)
blocks = re.findall(r"<diagram\b[\s\S]*?</diagram>", content)
ids = re.findall(r"<diagram\b[^>]*?\sid=\"([^\"]+)\"", content)

assert len(blocks) == len(ids), f"id/block mismatch: {len(ids)} vs {len(blocks)}"

OUT_DIR.mkdir(parents=True, exist_ok=True)

for i, (diagram_id, block) in enumerate(zip(ids, blocks), start=1):
    safe = re.sub(r"[^a-zA-Z0-9_-]+", "_", diagram_id).strip("_")
    fname = f"{i:02d}_{safe}.drawio"
    body = f"{mxfile_tag}\n    {block}\n</mxfile>\n"
    (OUT_DIR / fname).write_text(body, encoding="utf-8")
    print(f"wrote {fname}  ({len(block)} bytes)")

print(f"\nDone. {len(blocks)} files written to {OUT_DIR}")
