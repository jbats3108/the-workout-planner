import { describe, expect, it } from 'vitest';
import { pickLanIpv4, type LanIface } from './detectLanHost';

function iface(address: string, overrides: Partial<LanIface> = {}): LanIface {
    return {
        address,
        family: 'IPv4',
        internal: false,
        ...overrides,
    };
}

describe('pickLanIpv4', () => {
    it('prefers wifi over docker and bridge interfaces', () => {
        const address = pickLanIpv4({
            docker0: [iface('172.17.0.1')],
            'br-abc': [iface('172.19.0.1')],
            wlp4s0: [iface('192.168.0.131')],
        });

        expect(address).toBe('192.168.0.131');
    });

    it('prefers ethernet when wifi is absent', () => {
        const address = pickLanIpv4({
            docker0: [iface('172.17.0.1')],
            enp3s0: [iface('10.0.0.42')],
        });

        expect(address).toBe('10.0.0.42');
    });

    it('skips internal and IPv6 addresses', () => {
        const address = pickLanIpv4({
            lo: [iface('127.0.0.1', { internal: true })],
            wlp4s0: [iface('fe80::1', { family: 'IPv6' }), iface('192.168.1.20')],
        });

        expect(address).toBe('192.168.1.20');
    });

    it('falls back to an unnamed physical iface when no wifi/eth match', () => {
        const address = pickLanIpv4({
            docker0: [iface('172.17.0.1')],
            'br-x': [iface('172.18.0.1')],
            usb0: [iface('192.168.42.1')],
        });

        expect(address).toBe('192.168.42.1');
    });

    it('returns null when only virtual interfaces exist', () => {
        expect(
            pickLanIpv4({
                lo: [iface('127.0.0.1', { internal: true })],
                docker0: [iface('172.17.0.1')],
            }),
        ).toBeNull();
    });

    it('treats numeric family 4 as IPv4', () => {
        const address = pickLanIpv4({
            en0: [iface('192.168.0.5', { family: 4 as LanIface['family'] })],
        });

        expect(address).toBe('192.168.0.5');
    });
});
