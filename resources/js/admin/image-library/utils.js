const formatBytes = (value) => {
  const bytes = Number(value);

  if (!Number.isFinite(bytes) || bytes <= 0) {
    return null;
  }

  if (bytes < 1024) {
    return `${Math.round(bytes)} B`;
  }

  const units = ['KB', 'MB', 'GB', 'TB'];
  let size = bytes / 1024;
  let unitIndex = 0;

  while (size >= 1024 && unitIndex < units.length - 1) {
    size /= 1024;
    unitIndex += 1;
  }

  const fixed = size >= 10 ? Math.round(size) : Math.round(size * 10) / 10;

  return `${fixed} ${units[unitIndex]}`;
};

const normaliseImage = (item) => {
  if (!item || typeof item !== 'object') {
    return null;
  }

  const id = item.id ?? item.uuid ?? item.image_id ?? null;

  if (!id) {
    return null;
  }

  const width = item.width ?? item.dimensions?.width ?? null;
  const height = item.height ?? item.dimensions?.height ?? null;

  const sizeValue =
    item.size_human ??
    item.size_label ??
    (typeof item.size_bytes === 'number' ? formatBytes(item.size_bytes) : null) ??
    (typeof item.size === 'number' ? formatBytes(item.size) : null);

  return {
    ...item,
    id,
    url: item.url ?? item.path ?? '',
    display_name:
      item.display_name ??
      item.name ??
      item.original_name ??
      item.filename ??
      (typeof item.url === 'string' ? item.url.split('/').pop() : `Image ${id}`),
    size_human: sizeValue,
    dimensions_label:
      item.dimensions_label ?? (width && height ? `${width}×${height}` : null),
    updated_at_human:
      item.updated_at_human ??
      item.updated_at ??
      item.created_at_human ??
      item.created_at ??
      item.last_modified_human ??
      null,
    last_modified_human:
      item.last_modified_human ??
      item.updated_at_human ??
      item.updated_at ??
      item.created_at_human ??
      item.created_at ??
      null,
  };
};

const normaliseCollection = (value) => {
  if (!Array.isArray(value)) {
    return [];
  }

  return value
    .map((item) => normaliseImage(item))
    .filter((item) => Boolean(item));
};

const normaliseTypes = (value) => {
  if (!Array.isArray(value)) {
    return [];
  }

  const deduped = Array.from(
    new Set(
      value
        .map((item) => (typeof item === 'string' ? item : null))
        .filter((item) => Boolean(item) && item.trim().length > 0),
    ),
  );

  return deduped.sort((a, b) => a.localeCompare(b));
};

export { formatBytes, normaliseCollection, normaliseImage, normaliseTypes };
