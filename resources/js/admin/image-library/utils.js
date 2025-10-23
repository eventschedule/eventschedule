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

  const source = item.image && typeof item.image === 'object' ? { ...item, ...item.image } : item;

  const id =
    source.id ??
    source.uuid ??
    source.image_id ??
    source.identifier ??
    null;

  if (id === undefined || id === null || id === '') {
    return null;
  }

  const width =
    source.width ??
    source.dimensions?.width ??
    source.image_width ??
    null;
  const height =
    source.height ??
    source.dimensions?.height ??
    source.image_height ??
    null;

  const sizeValue =
    source.size_human ??
    source.size_label ??
    (typeof source.size_bytes === 'number' ? formatBytes(source.size_bytes) : null) ??
    (typeof source.size === 'number' ? formatBytes(source.size) : null);

  const url =
    source.url ??
    source.path ??
    source.image_path ??
    (source.file?.url ?? source.file?.path) ??
    '';

  const displayName =
    source.display_name ??
    source.name ??
    source.original_name ??
    source.filename ??
    (typeof url === 'string' && url.includes('/') ? url.split('/').pop() : null) ??
    `Image ${id}`;

  return {
    ...item,
    ...source,
    id,
    url,
    display_name: displayName,
    size_human: sizeValue,
    dimensions_label:
      source.dimensions_label ?? (width && height ? `${width}×${height}` : null),
    updated_at_human:
      source.updated_at_human ??
      source.updated_at ??
      source.created_at_human ??
      source.created_at ??
      source.last_modified_human ??
      null,
    last_modified_human:
      source.last_modified_human ??
      source.updated_at_human ??
      source.updated_at ??
      source.created_at_human ??
      source.created_at ??
      null,
  };
};

const toIterable = (value) => {
  if (!value) {
    return [];
  }

  if (Array.isArray(value)) {
    return value;
  }

  if (typeof value === 'object') {
    if (Array.isArray(value.data)) {
      return value.data;
    }

    return Object.values(value);
  }

  return [];
};

const normaliseCollection = (value) => {
  const iterable = toIterable(value);
  const seen = new Set();
  const normalised = [];

  iterable.forEach((item) => {
    const image = normaliseImage(item);
    if (image && !seen.has(image.id)) {
      seen.add(image.id);
      normalised.push(image);
    }
  });

  return normalised;
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
