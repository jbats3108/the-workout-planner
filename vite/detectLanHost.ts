import { networkInterfaces, type NetworkInterfaceInfo } from 'node:os';

/** Virtual / container bridges — never a phone-reachable LAN address. */
const SKIP_IFACE = /^(lo|docker\d*|br-|veth|virbr|vmnet|vboxnet|tun|tap|wg|zt|cni|flannel|lxc|podman)/i;

/** Wi-Fi / Ethernet naming on Linux, macOS, and Windows. */
const PREFER_IFACE = /^(wl|wlan|wlp|en|eth|enp|eno|ens)/i;

export type LanIface = Pick<NetworkInterfaceInfo, 'address' | 'family' | 'internal'>;

/**
 * Pick a phone-reachable IPv4 from os.networkInterfaces()-shaped data.
 * Prefers wifi/ethernet; skips docker and bridge interfaces.
 */
export function pickLanIpv4(interfaces: NodeJS.Dict<LanIface[] | undefined>): string | null {
    const preferred: string[] = [];
    const fallback: string[] = [];

    for (const [name, ifaces] of Object.entries(interfaces)) {
        if (SKIP_IFACE.test(name)) {
            continue;
        }

        for (const iface of ifaces ?? []) {
            const family = iface.family as string | number;
            const isV4 = family === 'IPv4' || family === 4;

            if (!isV4 || iface.internal) {
                continue;
            }

            if (PREFER_IFACE.test(name)) {
                preferred.push(iface.address);
            } else {
                fallback.push(iface.address);
            }
        }
    }

    return preferred[0] ?? fallback[0] ?? null;
}

export function detectLanIpv4(interfaces: NodeJS.Dict<LanIface[] | undefined> = networkInterfaces()): string | null {
    return pickLanIpv4(interfaces);
}
