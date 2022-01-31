export abstract class uuidGenerator {

  /**
   * Gera um UUID aleatório para ser usado no front-end. Ex: 'bf1784b2-f70d-f2c1-3f50-474da6fbb313'
   * @returns UUID {string}
   */
  public static generate(): string {
    const UUID = `${this.S4()}${this.S4()}-${this.S4()}-${this.S4()}-${this.S4()}-${this.S4()}${this.S4()}${this.S4()}`;

    return UUID;
  }

  private static S4(): string {
    return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
  }
}
