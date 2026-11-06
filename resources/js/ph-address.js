// Nominatim (OpenStreetMap) labels Philippine areas differently from its field names:
// `state` holds the province and `region` holds the region. Metro Manila has neither a
// province nor a `state`, and its `state_district` is a district such as "Capital District".
// Independent cities such as Cebu City and Baguio come back with no province at all.
export default function philippineProvinceAndRegion(address = {}) {
    const region = address.region || '';
    const province = address.province || address.state || (region === 'Metro Manila' ? 'Metro Manila' : '');

    return { province, region };
}
